pipeline {
    agent any

    environment {
        DOCKER_ID = credentials('DOCKER_ID_THIAGO')
        DOCKER_PASSWORD = credentials('DOCKER_PASSWORD_THIAGO')
        GITHUB_TOKEN = credentials('GITHUB_SSH_THIAGO')
        VAULT_ADDR = 'http://vault:8200'
    }

    stages {
        stage('Verify tooling') {
            steps {
                sh '''
                    docker version
                    docker info
                    curl --version
                '''
            }
        }
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        stage('Prune Docker data'){
            steps {
                sh 'docker container prune -f'
                sh 'docker image prune -f'
            }
        }
        stage('Fetch secrets & Deploy') {
            steps {
                withVault(
                    configuration: [
                        vaultUrl: "${VAULT_ADDR}",
                        vaultCredentialId: 'jenkins-approle-credential'
                    ],
                    vaultSecrets: [
                        [
                            path: 'secret/realizalab-api',
                            secretValues: [
                                [envVar: 'APP_KEY', vaultKey: 'APP_KEY'],
                                [envVar: 'DB_DATABASE', vaultKey: 'DB_DATABASE'],
                                [envVar: 'DB_USERNAME', vaultKey: 'DB_USERNAME'],
                                [envVar: 'DB_PASSWORD', vaultKey: 'DB_PASSWORD'],
                                [envVar: 'REDIS_PASSWORD', vaultKey: 'REDIS_PASSWORD'],
                                [envVar: 'AWS_ACCESS_KEY_ID', vaultKey: 'AWS_ACCESS_KEY_ID'],
                                [envVar: 'AWS_SECRET_ACCESS_KEY', vaultKey: 'AWS_SECRET_ACCESS_KEY'],
                                [envVar: 'AWS_DEFAULT_REGION', vaultKey: 'AWS_DEFAULT_REGION'],
                                [envVar: 'AWS_BUCKET', vaultKey: 'AWS_BUCKET'],
                                [envVar: 'AWS_ENDPOINT', vaultKey: 'AWS_ENDPOINT']
                            ]
                        ]
                    ]
                ) {
                    sh '''
                        cat > .env <<EOF
APP_KEY=$APP_KEY
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD
REDIS_PASSWORD=$REDIS_PASSWORD
AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY
AWS_DEFAULT_REGION=$AWS_DEFAULT_REGION
AWS_BUCKET=$AWS_BUCKET
AWS_ENDPOINT=$AWS_ENDPOINT
EOF
                        echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_ID" --password-stdin
                        docker-compose -f ./deploy/docker-compose.yml down --rmi 'local'
                        docker-compose -f ./deploy/docker-compose.yml --env-file .env build --build-arg GITHUB_TOKEN="$GITHUB_TOKEN"
                        docker-compose -f ./deploy/docker-compose.yml --env-file .env up -d
                        docker-compose -f ./deploy/docker-compose.yml ps
                        rm -f .env
                    '''
                }
            }
        }
        stage('Wait for API') {
            steps {
                sh '''
                    echo "Aguardando realizalab-api responder..."
                    for i in $(seq 1 60); do
                        if docker exec realizalab-api curl -fsS -o /dev/null http://127.0.0.1/up; then
                            echo "API no ar."
                            exit 0
                        fi
                        if [ "$i" -eq 60 ]; then
                            echo "API nao respondeu /up em 2 minutos."
                            docker logs --tail 40 realizalab-api
                            exit 1
                        fi
                        sleep 2
                    done
                '''
            }
        }
        stage('Clear cache') {
            steps {
                sh 'docker exec realizalab-api php artisan config:cache'
                sh 'docker exec realizalab-api php artisan migrate --force'
                sh 'docker exec realizalab-api php artisan storage:link'
            }
        }
    }
    post {
        always {
            sh 'docker builder prune -f --filter "until=168h" || true'
            sh 'rm -f .env || true'
        }
    }
}
