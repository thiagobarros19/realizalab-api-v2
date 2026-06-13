pipeline {
    agent any
    stages {
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
