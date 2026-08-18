<?php

	// Credenciales de Supabase
//	const SERVER="https://uxzthiwwpxurhlcbecwh.supabase.com"; // Reemplaza con el host que te da Supabase en los settings de tu base de datos
//	const PORT="6543"; // Generalmente Supabase recomienda el puerto 6543 para conexiones desde aplicaciones (Connection Pooler) o 5432.
//	const DB="postgres"; // La base de datos por defecto en Supabase casi siempre es "postgres"
	
//	const USER="postgres.quagliamarinmiguel-tech"; // Tu usuario de Supabase (reemplaza las x con tu id de proyecto)
//	const PASS="1234"; // La contraseña de la base de datos de tu proyecto

	// ⚠️ IMPORTANTE: Cambiamos "mysql" por "pgsql" y añadimos el puerto
//	const SGBD="pgsql:host=".SERVER.";port=".PORT.";dbname=".DB;


//	const METHOD="AES-256-CBC";
//	const SECRET_KEY='$CEPROCA@2020';
//	const SECRET_IV='037970';




// ////// aqui intente pero parece q hay un errorde uso devariables y constantes

//const SERVER = "db.uxzthiwwpxurhlcbecwh.supabase.co"; // Host
//const PORT   = "5432";
//const DB     = "postgres";
//const USER   = "postgres";
//const PASS   = "1234";

// DSN PDO
//$dsn = "pgsql:host=" . SERVER . ";port=" . PORT . ";dbname=" . DB . ";sslmode=require";

//$pdo = new PDO($dsn, USER, PASS, [
//  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//]);



//aqui toy intentando pero ahora usando const SGBD
const HOST = "db.uxzthiwwpxurhlcbecwh.supabase.co";
const PORT = "5432";
const DB   = "postgres";
const USER = "postgres";
const PASS = "1234";

const SGBD = "pgsql:host=" . HOST . ";port=" . PORT . ";dbname=" . DB . ";sslmode=require";