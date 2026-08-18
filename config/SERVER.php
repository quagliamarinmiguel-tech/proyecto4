<?php

	// Credenciales de Supabase
	const SERVER="https://uxzthiwwpxurhlcbecwh.supabase.co"; // Reemplaza con el host que te da Supabase en los settings de tu base de datos
	const PORT="6543"; // Generalmente Supabase recomienda el puerto 6543 para conexiones desde aplicaciones (Connection Pooler) o 5432.
	const DB="postgres"; // La base de datos por defecto en Supabase casi siempre es "postgres"
	
	const USER="postgres.quagliamarinmiguel-tech"; // Tu usuario de Supabase (reemplaza las x con tu id de proyecto)
	const PASS="1234"; // La contraseña de la base de datos de tu proyecto

	// ⚠️ IMPORTANTE: Cambiamos "mysql" por "pgsql" y añadimos el puerto
	const SGBD="pgsql:host=".SERVER.";port=".PORT.";dbname=".DB;


	const METHOD="AES-256-CBC";
	const SECRET_KEY='$CEPROCA@2020';
	const SECRET_IV='037970';
