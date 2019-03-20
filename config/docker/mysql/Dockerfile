FROM mysql:5.7

ARG MYSQL_USER
ARG MYSQL_PASSWORD
ARG MYSQL_PORT

# Securise line command "mysql" and "mysqldump" (you don't need to specify user + password)
RUN printf "[client]\n\
host=localhost\n\
user=$MYSQL_USER\n\
password=$MYSQL_PASSWORD\n\
port=$MYSQL_PORT\n\
default-character-set=utf8" >> ~/.my.cnf
