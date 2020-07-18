def buildStateHasChanged = false;

pipeline {
    agent any
    stages {
        stage('build') {
            steps {
                sh 'composer72 install'
            }
        }
        
        stage('deploy') {
            steps {
                sh 'echo $GIT_BRANCH'
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
