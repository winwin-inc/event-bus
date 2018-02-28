def buildStateHasChanged = false;

pipeline {
    agent any
    stages {
        stage('build') {
            steps {
                sh 'composer install'
                sh 'cp tests/env.ci tests/.env'
                sh 'db.phar sync --env tests/.env --engine memory -n -t resources/schema.yaml'
                sh 'winner.phar lint src/'
                sh 'php -d memory_limit=-1 vendor/bin/phpunit tests'
            }
        }
    }

    post {
        success {
            script {
                if (buildStateHasChanged == true) {
                    echo "Notify for success because build state has changed..."
                    sendNotification('SUCCESS')
                }
            }
        }
        failure {
            sendNotification('FAILURE')
        }
        changed {
            echo "Build state has changed..."
            script {
                buildStateHasChanged = true
            }
        }
    }    
}

def sendNotification(status) {
    sh "jenkins-notify -j ${env.JOB_NAME} -s ${status} -u ${env.BUILD_URL}"
}
