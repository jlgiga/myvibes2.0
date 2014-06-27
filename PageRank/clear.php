<?php

pg_connect("host=localhost port=5432 dbname=myvibes user=myvibes password=tseree");

pg_query("DELETE from friends_pr");

?>
