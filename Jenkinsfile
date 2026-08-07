pipeline {
    agent any

    environment {
        DOCKER_ID = credentials('DOCKER_ID_THIAGO')
        DOCKER_PASSWORD = credentials('DOCKER_PASSWORD_THIAGO')
        VAULT_ADDR = 'http://vault:8200'
        PROJECT = 'realizalab-api'
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
                            path: "secret/${PROJECT}",
                            secretValues: [
                                [envVar: 'APP_KEY', vaultKey: 'APP_KEY'],
                                [envVar: 'DB_NAME', vaultKey: 'DB_NAME'],
                                [envVar: 'DB_USER', vaultKey: 'DB_USER'],
                                [envVar: 'DB_PASSWORD', vaultKey: 'DB_PASSWORD'],
                                [envVar: 'REDIS_PASSWORD', vaultKey: 'REDIS_PASSWORD']
                            ]
                        ]
                    ]
                ) {
                    sh '''
                        cat > .env <<EOF
APP_KEY=$APP_KEY
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD
REDIS_PASSWORD=$REDIS_PASSWORD
EOF
                        echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_ID" --password-stdin
                        docker-compose -f ./deploy/docker-compose.yml down --rmi 'local'
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
