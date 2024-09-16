# scheduling_app
Scheduling application

# start app

# docker environment
docker-compose up --build

# komanda za ulazak u docker php container
docker-compose exec php bash

# komanda za ulazak u docker sql container
docker-compose exec database mysql -u root -p
password: secret

# podizanje projekta
1. Podizanje docker okruzenja. Ukoliko projekat vec build-van mozemo i bez --build opcije.
    Command:
    docker-compose up --build
2. Kreiranje baze
   - Iz php container obrisemo postojecu bazu ukoliko postoji.
        Command:
        bin/console doctrine:database:drop --force
   - Iz sql container kreiramo bazu scheduling_app
        Command:
        CREATE DATABASE scheduling_app;
   - Iz php container pokrecemo doctrine migracije koje kreiraju tabele u bazi
        Command:
        bin/console doctrine:migrations:migrate
   - Iz sql container mozemo dodati admin korisnika kako ga ne bi dodavali manuelno
          email: admin@admin.com 
          password:Test123!
          Command:
          INSERT INTO scheduling_app.user (id, email, roles, password, is_verified) VALUES (1, 'admin@admin.com', '["ROLE_ADMIN"]', '$2y$13$OqnEzS6wYr0RlzTwkaCbi.cuRPslch.CTNi.RTqdgnkzln6i/fOja', 1);
            
