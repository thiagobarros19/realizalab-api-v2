pipeline {
    agent any

    environment {
        DOCKER_ID = credentials('DOCKER_ID_THIAGO')
        DOCKER_PASSWORD = credentials('DOCKER_PASSWORD_THIAGO')
        GITHUB_TOKEN = credentials('GITHUB_CREDS_THIAGO')
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
                                [envVar: 'REDIS_PASSWORD', vaultKey: 'REDIS_PASSWORD']
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
EOF
                        echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_ID" --password-stdin
                        docker compose -f ./deploy/docker-compose.yml down --rmi 'local'
                        docker compose -f ./deploy/docker-compose.yml --env-file .env build --build-arg GITHUB_TOKEN="$GITHUB_TOKEN"
                        docker compose -f ./deploy/docker-compose.yml --env-file .env up -d
                        docker compose -f ./deploy/
