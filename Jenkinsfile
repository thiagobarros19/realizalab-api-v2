pipeline {
    agent any
    stages {
        stage('Docker Hub login') {
            steps {
                withCredentials([
                    string(credentialsId: 'DOCKER_ID_THIAGO', variable: 'DOCKERHUB_USER'),
                    string(credentialsId: 'DOCKER_PASSWORD_THIAGO', variable: 'DOCKERHUB_TOKEN')
                ]) {
                    sh 'echo "$DOCKERHUB_TOKEN" | docker login -u "$DOCKERHUB_USER" --password-stdin'
                }
            }
        }
        stage('Verify tooling') {
            steps {
                sh '''
                    docker version
                    docker info
                    docker-compose version
                    curl --version
                '''
            }
        }
        stage('Prune Docker data'){
            steps {
                sh 'docker system prune -a --volumes -f'
            }
        }
        stage('Start container') {
            steps {
                sh 'docker-compose -f ./deploy/docker-compose.yml up -d'
                sh 'docker-compose -f ./deploy/docker-compose.yml ps'
            }
        }
        stage('Clear cache') {
            steps {
                sh 'docker exec realizalab-api php artisan config:cache'
                sh 'docker exec realizalab-api php artisan route:list'
            }
        }
    }
}
