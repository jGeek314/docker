## Mysql / phpmyadmin docker-compose setup

Making a stab at docker with some old php/mysql apps.

To initialize the mysql database with an existing data dump, put the sql file in the mysql/init folder.
make sure to include the enviornment variables in the ".env" file.  Because I might use the same setup for
multiple apps, I'm using a container-name prefix enviornment variable so that the different instances will be 
recognizable after I move them to production.