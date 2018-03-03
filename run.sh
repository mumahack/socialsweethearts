docker stop $(docker ps -a -q)
docker build -t my-app .
docker run -d -p 8080:80 my-app
