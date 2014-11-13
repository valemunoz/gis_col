<?php

	function pgSql_db()
	{

	  $HostDB="localhost";
    $PortDB="5432";          
    $UserDB="postgres"; 
    $PassDB="12345";
    $NameDB="colombia_gis_sis";
		$dbPg = pg_connect("host=$HostDB port=$PortDB dbname=$NameDB user=$UserDB password=$PassDB");		 

	
return $dbPg;
	}
	function pgSql_db2()
	{
		 $HostDB="localhost";
    $PortDB="5432";          
    $UserDB="postgres"; 
    $PassDB="12345";
    $NameDB="colombia_app";
   
    
		$dbPg = pg_connect("host=$HostDB port=$PortDB dbname=$NameDB user=$UserDB password=$PassDB");		 	
		return $dbPg;
	}
	function pgSql_db3()
	{
		 $HostDB="localhost";
    $PortDB="5432";          
    $UserDB="postgres"; 
    $PassDB="12345";
    $NameDB="colombia_gis";
   
    
		$dbPg = pg_connect("host=$HostDB port=$PortDB dbname=$NameDB user=$UserDB password=$PassDB");		 	
		return $dbPg;
	}
?>
