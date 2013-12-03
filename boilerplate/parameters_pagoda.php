<?php

if ( isset($_SERVER["DB1_NAME"]) )
{
    // Keep your credentials out of your repo!

    // • Database Info; DB vars provided in PagodaBox environment
    $container->setParameter('database_name',     $_SERVER["DB1_NAME"]);
    $container->setParameter('database_host',     $_SERVER["DB1_HOST"]);
    $container->setParameter('database_port',     $_SERVER["DB1_PORT"]);
    $container->setParameter('database_user',     $_SERVER["DB1_USER"]);
    $container->setParameter('database_password', $_SERVER["DB1_PASS"]);

    // • Symfony Secret; provided by app developer (you)
    // go to: PagodBox > Admin > Environment > Vars > Add Another
    // "SYMFONY_SECRET = whateveryouwant"
    $container->setParameter('secret', $_SERVER["SYMFONY_SECRET"]);
}
