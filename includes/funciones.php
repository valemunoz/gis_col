<?php
include('connec.php');
define("PATH_SITE","http://localhost/github/gis_col");

function buscarDireccionOSM($query)
{
	
	$delay = 0;
	
	$base_url="http://nominatim.openstreetmap.org/search?";
  $geocode_pending = true;
  while ($geocode_pending) {
    //$address = "pasaje u 2113 chile";
    $address=trim($direccion);
    $request_url = $base_url . "q=".urldecode($query)."&format=xml&polygon=1&addressdetails=1";
    $xml = simplexml_load_file($request_url) or die("url not loading");    
    //print_r($xml);
    //$status = $xml->status;
    $geocode_pending=false;
    //echo count($xml->place);
    foreach($xml->place as $place)
    {
    	$place=$xml->place;
    	if(is_numeric(trim($place->house_number)))
    	{
    		$lonlat=Array();
    		$lonlat[]=$place['lon'];
    		$lonlat[]=$place['lat'];
    		$lonlat[]=$place->house_number;
    		$lonlat[]=$place->road;
    		$lonlat[]=$place->city;
    		$lonlat[]=$place->country;
    		$lonlat[]=$place->state;
    		$lonlat_arr[]=$lonlat;
    	}
    	//echo "<br>".$longitud;
    	//print_r($xml_result);
    }
  }
  return $lonlat_arr;
}

function getDireccionGoogle($direccion)
{
	
	
	$delay = 0;
	//$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;
	$base_url="http://maps.googleapis.com/maps/api/geocode/xml?";
  $geocode_pending = true;
  while ($geocode_pending) {
    //$address = "pasaje u 2113 chile";
    $address=trim($direccion);
    $request_url = $base_url . "&address=" . urldecode($address)."&oe=utf-8&sensor=false";
    $xml = simplexml_load_file($request_url) or die("url not loading");    
    //print_r($xml);
    $status = $xml->status;
    if (strcmp($status, "OK") == 0) {
      // Successful geocode
      $geocode_pending = false;
      
      $total_r=$xml->result;
      
      $len_place=$xml;
      $i=1;
      foreach($len_place->result as $len)
      {
      	$direc = $len->formatted_address;
      	$tipo = $len->type;
      	if($tipo=="street_address")
      	{
      	//echo "total:".count($len->address_component);
      	for($i=0;$i<count($len->address_component);$i++)
      	{
      		$type=$len->address_component[$i]->type;
      		$type2=$len->address_component[$i]->type[0];
      		if(strtolower(trim($type))=="street_number")
      		{
      			$numero_municipal=$len->address_component[$i]->long_name;
      		}elseif(strtolower(trim($type))=="route")
      		{
      			$calle=$len->address_component[$i]->long_name;
      			$abrevia_calle=$len->address_component[$i]->short_name;
      		}elseif(strtolower(trim($type2))=="locality")
      		{
      			$ciudad=$len->address_component[$i]->long_name;
      			$abrevia_ciudad=$len->address_component[$i]->short_name;
      		}elseif(strtolower(trim($type2))=="administrative_area_level_3")
      		{
      			$comuna=$len->address_component[$i]->long_name;
      			$abrevia_comuna=$len->address_component[$i]->short_name;
      		}elseif(strtolower(trim($type2))=="administrative_area_level_1")
      		{
      			$region=$len->address_component[$i]->long_name;
      			$abrevia_region=$len->address_component[$i]->short_name;
      		}elseif(strtolower(trim($type2))=="country")
      		{
      			$pais=$len->address_component[$i]->long_name;
      			$abrevia_pais=$len->address_component[$i]->short_name;
      		}
      		
      		
      	}
      	//geometrias
      	$latitud=$len->geometry->location->lat;
      	$longitud=$len->geometry->location->lng;
      	$tipo_gis=$len->geometry->location_type;
      	
      	$dire=Array();
				$dire[]=$tipo;
				$dire[]=$direc;
				$dire[]=$numero_municipal;
				$dire[]=$calle;
				$dire[]=$comuna;
				$dire[]=$ciudad;
				$dire[]=$region;
				$dire[]=$latitud;
				$dire[]=$longitud;
				$dire[]=$tipo_gis;
      	$direccion_arr[]=$dire;
				$i++;
			}
    	}      
    } 
    usleep($delay);
  }
 
	return $direccion_arr;
}
function CM_getServiciosActivos($id_cliente)
{
	$dbPg=pgSql_db();	
	$sql2 = "select id_servicio_mx,nombre,id_gis_servicio,estado,prioridad,icono from mx_servicio where estado=0 and id_cliente=".$id_cliente." order by nombre";		
  $rs2 = pg_query($dbPg, $sql2);

	while ($row2 = pg_fetch_row($rs2))
		{
			$data=Array();
			$data[]=$row2[0];
			$data[]=$row2[1];
	   $data[]=$row2[2];
	   $data[]=$row2[3];	
	   $data[]=$row2[4];
	   $data[]=$row2[5];
			$datos[]=$data;
	}
	
	return $datos;
}
function CM_getServiciosActivosEspecificos($id_cliente)
{
	$dbPg=pgSql_db();	
	$sql2 = "select id_servicio_mx,nombre,id_gis_servicio,estado,prioridad,icono from mx_servicio where estado=10 and id_cliente=".$id_cliente." order by nombre";		
  $rs2 = pg_query($dbPg, $sql2);

	while ($row2 = pg_fetch_row($rs2))
		{
			$data=Array();
			$data[]=$row2[0];
			$data[]=$row2[1];
	   $data[]=$row2[2];
	   $data[]=$row2[3];	
	   $data[]=$row2[4];
	   $data[]=$row2[5];
			$datos[]=$data;
	}
	
	return $datos;
}
function getServicioExtend($id_serv,$latI,$latS,$lonD,$lonI)
{
	$dbPg=pgSql_db2();	
	$sql2 = "SELECT id_categoria,latitud,longitud,calle,numero_municipal,comuna,region,id_comuna,id_region,categoria,nombre_servicio,id_servicio FROM gis_servicios where id_categoria=".$id_serv." and latitud<=".$latS." and latitud >=".$latI." and longitud <=".$lonD." and longitud >=".$lonI."";		
  $rs2 = pg_query($dbPg, $sql2);
	//echo "<br>".$sql2;
	while ($row2 = pg_fetch_row($rs2))
		{
			$data=Array();
			$data[]=$row2[0];
			$data[]=$row2[1];
	   $data[]=$row2[2];
	   $data[]=$row2[3];	
	   $data[]=$row2[4];	
	   $data[]=$row2[5];	
	   $data[]=$row2[6];	
	   $data[]=$row2[7];	
	   $data[]=$row2[8];	
	   $data[]=$row2[9];	
	   $data[]=$row2[10];	
	   $data[]=$row2[11];	
			$datos[]=$data;
	}
	return $datos;
}
function getServicioExtendFull($query,$latI,$latS,$lonD,$lonI)
{
	$dbPg=pgSql_db2();
	$query=elimina_acentos($query);
	$sql2 = "SELECT id_categoria,latitud,longitud,calle,numero_municipal,comuna,region,id_comuna,id_region,categoria,nombre_servicio FROM gis_servicios where estado=0 and latitud<=".$latS." and latitud >=".$latI." and longitud <=".$lonD." and longitud >=".$lonI." and query_completa like '%".trim($query)."%' limit 10";		
  $rs2 = pg_query($dbPg, $sql2);
	//echo "<br>".$sql2;
	while ($row2 = pg_fetch_row($rs2))
		{
			$data=Array();
			$data[]=$row2[0];
			$data[]=$row2[1];
	   $data[]=$row2[2];
	   $data[]=$row2[3];	
	   $data[]=$row2[4];	
	   $data[]=$row2[5];	
	   $data[]=$row2[6];	
	   $data[]=$row2[7];	
	   $data[]=$row2[8];	
	   $data[]=$row2[9];	
	   $data[]=$row2[10];	
			$datos[]=$data;
	}
	return $datos;
}



function getServiciosXRadio($lat,$lon,$radio)
{
	$dbPg=pgSql_db2();		
	
	$sql="select id_servicio,nombre_servicio,categoria,latitud,longitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  )/1000 as radio,calle,numero_municipal,comuna from gis_servicios where ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  )/1000 < ".$radio."";
	$sql .=" order by radio limit 30";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5]; //radio
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direcciones[]=$direc;
		
		
	}	
  return $direcciones;
}
function getServiciosFull($query,$lat,$lon,$radio)
{
	$dbPg=pgSql_db2();		
	$query=strtolower(elimina_acentos(urldecode($query)));
	$sql="select id_servicio,nombre_servicio,categoria,latitud,longitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) as radio,calle,numero_municipal,comuna,descripcion,'2' from gis_servicios where estado=0 and query_completa like '%".$query."%' and ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) <= ".$radio."";
	$sql .=" order by radio";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5]; //radio
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direc[]=$rowCalle[9];
		$direc[]=$rowCalle[10];
		$direcciones[]=$direc;
		
		
	}	
  return $direcciones;
}
function getServiciosContein($coordenadas)
{
	$dbPg=pgSql_db2();
	
  $sql2 = "select nombre_servicio,latitud,longitud from gis_servicios where ST_Contains(ST_GeomFromText('".$coordenadas."',4326),ST_GeomFromText(st_asText(geom),4326)) limit 30";		
  //echo "<br>".$sql2;
  $rs2 = pg_query($dbPg, $sql2);
	while ($row2 = pg_fetch_row($rs2))
	{
		$data=Array();
		$data[]=$row2[0];
		$data[]=$row2[1];
		$data[]=$row2[2];
		$datas[]=$data;
	}
	return $datas;
}
function elimina_acentos($cadena)
{
	
	$tofind = "¿¡¬√ƒ≈‡·‚„‰Â“”‘’÷ÿÚÛÙıˆ¯»… ÀËÈÍÎ«ÁÃÕŒœÏÌÓÔŸ⁄€‹˘˙˚¸ˇ—Ò";
	$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
	return(strtr($cadena,$tofind,$replac));
}
function getServiciosXId($id)
{
	$dbPg=pgSql_db2();		
	
	$sql="select id_servicio,nombre_servicio,categoria,latitud,longitud,calle,numero_municipal,comuna,descripcion from gis_servicios where id_servicio=".$id." and estado=0";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		
		$direc[]=$rowCalle[5];
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		
		
		
	}	
  return $direc;
}

function createCsv($productos,$nom_file)
{
	$out = fopen('../csv/'.$nom_file.'', 'w');
	fputcsv($out, array('Id', 'Titulo','Direccion','tipo','contenido'),';');
	//print_r($productos);
	foreach($productos as $key => $prod)
	{
					$estado=$prod[3];
					if($estado==0)
					  $estado="Aceptada";
					elseif($estado==1)  
						$estado="Rechazada";
		fputcsv($out, array(''.$prod[0].'', ''.$prod[1].'',''.$prod[2].'', ''.$prod[3].'',''.$prod[4].''),';');
	}
	fclose($out);
	
}
function getDireccionRadio($lon,$lat,$radio,$radio_ini)
{
	$dbPg=pgSql_db2();		
	
	$sql="select id_direccion,calle,numero_municipal,comuna,longitud,latitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  )/1000 as radio from gis_direccion where ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))) < ".$radio." and ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))) > ".$radio_ini."";
	$sql .=" order by radio";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5];
		$direc[]=$rowCalle[6];
		
		$lonlat[]=$rowCalle[4]." ".$rowCalle[5];
		$direcciones[]=$direc;
		
		
	}	
	//$poligono=getPoligono(implode($lonlat,",").",".$lonlat[0]);
  return array($direcciones,$poligono,$lonlat);
}
function getPoligono($data)
{
	$dbPg=pgSql_db2();		
	
	$sql="select st_asText(ST_ConvexHull(ST_GeomFromText('MULTIPOLYGON(((".trim($data).")))',2276)))";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle))
	{		
		$datos=$rowCalle[0];
	}	
  return $datos;
}
/*Sesion*/
function getUsuario($mail)
{
	$dbPg=pgSql_db();	
	$sql2 = "select id_usuario, nombre, mail, clave, fecha_registro, estado, fecha_inicio, 
       fecha_termino, id_cliente from gis_usuario where mail like '".strtolower($mail)."' and estado=0";		
  $rs2 = pg_query($dbPg, $sql2);

	while ($row2 = pg_fetch_row($rs2))
	{			
		 $data[]=$row2[0];
		 $data[]=$row2[1];
	   $data[]=$row2[2];
	   $data[]=$row2[3];	
	   $data[]=$row2[4];	   
	   $data[]=$row2[5];	 
	   
	   $data[]=$row2[6];	 
	   $data[]=$row2[7];	 
	   $data[]=$row2[8];	 
	   	 
	}
	pg_close($dbPg);
	return $data;
}
function inicioSesion($mail,$nombre,$id_usuario,$cliente,$app)
{
	
	session_start();	
	//session_register('usuario');
	$_SESSION["us_mail"] = $mail;
	$_SESSION["us_id"] = $id_usuario;
	$_SESSION['us_fecha']=date("Y-m-d H:i:s");
	$_SESSION['us_nombre']=$nombre;
	$_SESSION['us_id_cli']=$cliente;
	$_SESSION['us_apps']=$app;	
	$cli=getCliente($_SESSION['us_id_cli']);
	$_SESSION['us_pais']=$cli[3];
}
function cerrar_sesion()
{
	session_start();
	unset($_SESSION["us_mail"]); 
	unset($_SESSION["us_fecha"]); 
	unset($_SESSION["us_nombre"]); 
	unset($_SESSION["us_id"]); 
	unset($_SESSION['us_id_cli']);
	unset($_SESSION['us_apps']);
	unset($_SESSION['us_pais']);
	unset($_SESSION['us_nom_pais']);
	//session_destroy();
}
function estado_sesion()
{
	session_start();
	
	$estado=1;
	$hoy=date("Y-m-d H:i:s");
	
	$tiempo= segundos($_SESSION['us_fecha'],$hoy);
	
	if(isset($_SESSION['us_mail']) and trim($_SESSION['us_mail'])!="" and $tiempo < 7200)	//7200
  {
  	$estado=0;
  }
  
  return $estado;
}

function segundos($hora_inicio,$hora_fin){
$hora_i=substr($hora_inicio,11,2);
$minutos_i=substr($hora_inicio,14,2);
$aÒo_i=substr($hora_inicio,0,4);
$mes_i=substr($hora_inicio,5,2);
$dia_i=substr($hora_inicio,8,2);
$hora_f=substr($hora_fin,11,2);
$minutos_f=substr($hora_fin,14,2);
$aÒo_f=substr($hora_fin,0,4);
$mes_f=substr($hora_fin,5,2);
$dia_f=substr($hora_fin,8,2);
$diferencia_seg=mktime($hora_f,$minutos_f,0,$mes_f,$dia_f,$aÒo_f) - mktime($hora_i,$minutos_i,0,$mes_i,$dia_i,$aÒo_i);
return $diferencia_seg;
}
function getCliente($id)
{
	$dbPg=pgSql_db2();		
	
	$sql="select  nombre, estado,logo,pais from doom_cliente where id_cliente=".$id."";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];	
		$data[]=$rowCalle[0];
		$data[]=$rowCalle[1];
		$data[]=$rowCalle[2];
		$data[]=$rowCalle[3];
		
		
	
	}	
	pg_close($dbPg);
  return $data;
}
/**/
function getFecha()
{
	$fecha=date("Y-m-d H:i:s");
	$fecha_actual2 = strtotime ( '-4 hours ' , strtotime ( $fecha ) ) ;
	$fec = date ( 'Y-m-d H:i:s' , $fecha_actual2 );
	return $fec;
}
function GetCapas($id_cliente)
{
	$dbPg=pgSql_db();		
	
	$sql="SELECT id_cli_capa, id_cliente, id_capa, estado FROM gis_cliente_capa where id_cliente=".$id_cliente." and estado=0";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];	
		$data[]=$rowCalle[2];
		
	}	
	pg_close($dbPg);
  return $data;
}

function addPoligono($puntos,$lat,$lon,$usuario,$nombre)
{
	$dbPg=pgSql_db();	
	$poli=$puntos;
	$poli=str_replace(","," ",$poli);
	$poli=str_replace("|",",",$poli);
	$sql="INSERT INTO gis_poligonos(
            puntos, longitud, latitud, id_usuario, estado,fecha_registro,nombre,geom)
    VALUES ('".$puntos."', '".$lon."', '".$lat."', '".$usuario."', 0,'".getFecha()."','".$nombre."',ST_GeomFromText('MULTIPOLYGON(((".$poli.")))',2276));";
	$rsCalle = pg_query($dbPg, $sql);	    
  $dbPg=pgSql_db();	
}
function getMisPoligonos($usuario)
{
	$dbPg=pgSql_db();		
	
	$sql="SELECT id_poligono, puntos, longitud, latitud, id_usuario, estado, nombre, 
       fecha_registro
  FROM gis_poligonos where id_usuario=".$usuario." and estado=0 order by fecha_registro desc;";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		$data=array();
		$data[]=$rowCalle[0];
		$data[]=$rowCalle[1];
		$data[]=$rowCalle[2];
		$data[]=$rowCalle[3];
		$data[]=$rowCalle[4];
		$data[]=$rowCalle[5];
		$data[]=$rowCalle[6];
		$data[]=$rowCalle[7];
		$datos[]=$data;
		
	}	
	pg_close($dbPg);
  return $datos;
}
function getMisPoligonosQR($qr)
{
	$dbPg=pgSql_db();		
	
	$sql="SELECT id_poligono, puntos, longitud, latitud, id_usuario, estado, nombre, 
       fecha_registro,geom
  FROM gis_poligonos where 1=1";	
	if($qr!="")
	{
		$sql .=$qr;
	}
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		$data=array();
		$data[]=$rowCalle[0];
		$data[]=$rowCalle[1];
		$data[]=$rowCalle[2];
		$data[]=$rowCalle[3];
		$data[]=$rowCalle[4];
		$data[]=$rowCalle[5];
		$data[]=$rowCalle[6];
		$data[]=$rowCalle[7];
		$data[]=$rowCalle[8];
		$datos[]=$data;
		
	}	
	pg_close($dbPg);
  return $datos;
}
function getServicioExtendEspecificos($id_serv,$latI,$latS,$lonD,$lonI)
{
	$dbPg=pgSql_db();	
	$sql2 = "SELECT id_gis_servicio, nombre, fecha_registro, estado, id_cliente, 
       geom, latitud, longitud, id_gis_categoria, descripcion, calle, 
       numero_municipal, comuna, region, query_completa FROM gis_servicios where id_gis_categoria=".$id_serv." and latitud<=".$latS." and latitud >=".$latI." and longitud <=".$lonD." and longitud >=".$lonI."";		
  $rs2 = pg_query($dbPg, $sql2);
	//echo "<br>".$sql2;
	while ($row2 = pg_fetch_row($rs2))
		{
			$data=Array();
			$data[]=$row2[0];
			$data[]=$row2[1];
	   $data[]=$row2[2];
	   $data[]=$row2[3];	
	   $data[]=$row2[4];	
	   $data[]=$row2[5];	
	   $data[]=$row2[6];	
	   $data[]=$row2[7];	
	   $data[]=$row2[8];	
	   $data[]=$row2[9];	
	   $data[]=$row2[10];	
	   $data[]=$row2[11];	
	   $data[]=$row2[12];	
	   $data[]=$row2[13];	
	   $data[]=$row2[14];	
			$datos[]=$data;
	}
	$dbPg=pgSql_db();	
	return $datos;
}
function getServiciosClienteXId($id)
{
	$dbPg=pgSql_db();		
	
	$sql="select id_gis_servicio,nombre,calle,numero_municipal,comuna,descripcion from gis_servicios where id_gis_servicio=".$id." and estado=0";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		
		$direc[]=$rowCalle[5];
		
		
		
	}	
  return $direc;
}

function getDireccionExacta($direccion,$limite)
{
	$dbPg=pgSql_db3();		
	
	$sql=utf8_encode("select id_direccion,calle,segmento,numero_municipal,comuna,region,latitud,longitud,query_completa,origen from gis_direccion where query_completa like '%".strtolower(trim($direccion))."%'");
	if($limite > 0)
	{
		$sql .=" limit ".$limite."";
	}
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=$rowCalle[1];
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5];
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];  
		 
		$direc[]=1;
		$direc[]=$rowCalle[9]; 
		$direcciones[]=$direc;
		
		
	}	
	pg_close($dbPg);
  return $direcciones;
}


function getServiciosXRadioEspecificos($lat,$lon,$radio)
{
	$dbPg=pgSql_db2();		
	
	$sql="select id_servicio,nombre_servicio,categoria,latitud,longitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) as radio,calle,numero_municipal,comuna,id_categoria from gis_servicios where ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) < ".$radio." and id_categoria in(48,74,13,71,72,41,23,73)";
	$sql .=" order by nombre_servicio";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5]; //radio
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direc[]=$rowCalle[9];
		$direcciones[]=$direc;
		
		
	}	
  return $direcciones;
}


function updatePoligono($puntos,$id)
{
	$dbPg=pgSql_db();	
	$sql="update gis_poligonos set puntos='".$puntos."' where id_poligono=".$id."";
	$rsCalle = pg_query($dbPg, $sql);	    
  $dbPg=pgSql_db();	
}

function getServiciosFullPoligono($query,$lat,$lon,$poligono)
{
	$dbPg=pgSql_db2();		
	$query=strtolower(elimina_acentos(urldecode($query)));
	$sql="select id_servicio,nombre_servicio,categoria,latitud,longitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) as radio,calle,numero_municipal,comuna,descripcion from gis_servicios where estado=0 and query_completa like '%".$query."%' and ST_Contains('".$poligono."',geom) ";
	$sql .=" order by radio";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5]; //radio
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direc[]=$rowCalle[9];
		$direcciones[]=$direc;
		
		
	}	
  return $direcciones;
}
function deletePoligono($id)
{
	$dbPg=pgSql_db();		
	
	$sql="delete from gis_poligonos where id_poligono=".$id."";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
}
function getCategoriasCliente($id)
{
	$dbPg=pgSql_db();		
	$sql="SELECT id_gis_categoria, nombre, fecha_inicio, fecha_termino, id_cliente FROM gis_categoria where id_cliente=".$id." and estado=0 order by nombre";	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$data=Array();
		$data[]=$rowCalle[0];
		$data[]=$rowCalle[1];
		$data[]=$rowCalle[2];
		$data[]=$rowCalle[3];
		$data[]=$rowCalle[4];		
		$datas[]=$data;
		
		
	}	
  return $datas;
}
function getServiciosClienteQR($qr)
{
	$dbPg=pgSql_db();		
	
	$sql="select id_gis_servicio,nombre,calle,numero_municipal,comuna,descripcion,latitud,longitud ,id_cliente,id_gis_categoria from gis_servicios where 1=1";	
	if(trim($qr!=""))
	{
		$sql .=$qr;
	}
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];		
		$direc[]=$rowCalle[5];
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direc[]=$rowCalle[9];
		$data[]=$direc;
		
		
	}	
  return $data;
}
function addCategoria($data)
{
	$dbPg=pgSql_db();		
	$sql="INSERT INTO gis_categoria(
             nombre,id_cliente, 
            estado)
    VALUES ('".$data[0]."', ".$data[1].", 
            0);";
	$rsCalle = pg_query($dbPg, $sql);	            
}
function getCategoriaCli($qr)
{
	$dbPg=pgSql_db();		
	
	$sql="select nombre,id_cliente,estado from gis_categoria where 1=1";	
	if(trim($qr!=""))
	{
		$sql .=$qr;
	}
	$rsCalle = pg_query($dbPg, $sql);	
	//echo "<br>".$sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		
		$data[]=$direc;
		
		
	}	
  return $data;
}
function UpdateCategoria($qr,$id)
{
	$dbPg=pgSql_db();		
	$sql="update gis_categoria set ".$qr." where id_gis_categoria=".$id."";
	$rsCalle = pg_query($dbPg, $sql);	            
}
function updateServicioCli($qr,$id)
{
	$dbPg=pgSql_db();		
	
	$sql="update gis_servicios set ".$qr." where id_gis_servicio=".$id."";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	
}

function addServicioCli($data)
{
	$dbPg=pgSql_db();		
	
	echo $sql="INSERT INTO gis_servicios(
            nombre, fecha_registro, estado, id_cliente, 
            geom, latitud, longitud, id_gis_categoria, descripcion, calle, 
            numero_municipal, comuna, region, query_completa)
    VALUES ('".$data[0]."', '".getFecha()."', 0, '".$_SESSION['us_id_cli']."', 
            ST_GeomFromText('POINT(".$data[1]." ".$data[2].")',2276), '".$data[2]."', '".$data[1]."', '".$data[3]."', '".$data[4]."', '".$data[5]."', 
            '".$data[6]."', '".$data[7]."', '', '".$data[0]." ".$data[5]." ".$data[6]." ".$data[7]." ".$data[4]."');";	
	
	$rsCalle = pg_query($dbPg, $sql);	
	
}

function getServiciosCliFull($query,$lat,$lon,$radio)
{
	$dbPg=pgSql_db();		
	$query=strtolower(elimina_acentos(urldecode($query)));
	$sql="select id_gis_servicio,nombre,id_gis_categoria,latitud,longitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) as radio,calle,numero_municipal,comuna,descripcion,'3' from gis_servicios where estado=0 and query_completa like '%".$query."%' and ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) <= ".$radio."";
	$sql .=" order by radio";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5]; //radio
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direc[]=$rowCalle[9];
		$direc[]=$rowCalle[10];
		$direcciones[]=$direc;
		
		
	}	
  return $direcciones;
}
function getServiciosCliFullPoligono($query,$lat,$lon,$poligono)
{
	$dbPg=pgSql_db();		
	$query=strtolower(elimina_acentos(urldecode($query)));
	$sql="select id_gis_servicio,nombre,id_gis_categoria,latitud,longitud,ST_Distance(
  ST_GeographyFromText('POINT(".$lon." ".$lat.")'), 
  ST_GeographyFromText(st_AsText(geom))
  ) as radio,calle,numero_municipal,comuna,descripcion from gis_servicios where estado=0 and query_completa like '%".$query."%' and ST_Contains('".$poligono."',geom) ";
	$sql .=" order by radio";
	
	
	$rsCalle = pg_query($dbPg, $sql);	
	//echo $sql;
	while ($rowCalle = pg_fetch_row($rsCalle)){		
		//$arr_callesComuna[$rowCalle[0]] = $rowCalle[1];
		$direc=Array();
		$direc[]=$rowCalle[0];
		$direc[]=trim($rowCalle[1]);
		$direc[]=$rowCalle[2];
		$direc[]=$rowCalle[3];
		$direc[]=$rowCalle[4];
		$direc[]=$rowCalle[5]; //radio
		$direc[]=$rowCalle[6];
		$direc[]=$rowCalle[7];
		$direc[]=$rowCalle[8];
		$direc[]=$rowCalle[9];
		$direcciones[]=$direc;
		
		
	}	
  return $direcciones;
}
function getPaises($qr)
{
	$dbPg=pgSql_db2();	
	$sql2 = "SELECT code,name,latitud,longitud from country where 1=1";		
	if($qr!="")
	{
		$sql2 .=$qr;
	}
	$sql2 .="order by name";
	//echo $sql2;
  $rs2 = pg_query($dbPg, $sql2);

	while ($row2 = pg_fetch_row($rs2))
	{			
		$data=array();
		 $data[]=$row2[0];
		 $data[]=$row2[1];
		 $data[]=$row2[2];
		 $data[]=$row2[3];
	   
	   $datos[]=$data;
	   	 
	}
	pg_close($dbPg);
	return $datos;
}
function addDireccion($dir,$origen)
{
  $dbPg=pgSql_db3();
	$sql=strtolower(utf8_encode(limpia(utf8_decode("insert into gis_direccion(pais,calle,numero_municipal,latitud,longitud,comuna,id_comuna,region,id_region,query_completa,geom,origen) values('COL','".$dir[0]."','".$dir[1]."','".$dir[4]."','".$dir[5]."','".$dir[2]."',0,'".$dir[3]."',0,'".$dir[6]."',ST_GeomFromText('POINT(".$dir[5]." ".$dir[4].")',2276),".$origen.")"))));
	//echo "<br>".$sql."<br>";
	$rsCalle = pg_query($dbPg, $sql);	
	pg_close($dbPg);
}
function limpia($cadena)
{
	$a_tofind = array('¿', '¡', '¬', '√', 'ƒ', '≈', '‡', '·', '‚', '„', '‰', 'Â'
   , '“', '”', '‘', '’', '÷', 'ÿ', 'Ú', 'Û', 'Ù', 'ı', 'ˆ', '¯'
   , '»', '…', ' ', 'À', 'Ë', 'È', 'Í', 'Î', '«', 'Á'
   , 'Ã', 'Õ', 'Œ', 'œ', 'Ï', 'Ì', 'Ó', 'Ô'
   , 'Ÿ', '⁄', '€', '‹', '˘', '˙', '˚', '¸', 'ˇ', '—', 'Ò');
$a_replac = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a'
   , 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o'
   , 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'C', 'c'
   , 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'
   , 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'y', 'N', 'n');
return $cadenaSinAcentos = str_replace($a_tofind, $a_replac, $cadena);
}
?>