<?php

/**
 * Configuración principal del sistema.
 * Para Supabase REST/PostgREST usando la clave anon.
 */

// URL del proyecto Supabase
const SUPABASE_URL = "https://uxzthiwwpxurhlcbecwh.supabase.co";

// Clave anon para consultas desde PHP/backend de confianza
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InV4enRoaXd3cHh1cmhsY2JlY3doIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODY3Nzg4NjgsImV4cCI6MjEwMjM1NDg2OH0.RgUH1FBCv3uZx4dLAi1oRoB-1UQLx4xC7R9ewofi938";

const SUPABASE_SECRET_KEY = "sb_secret_NwSXIBwWJWglNWJKvqv68g_RKUPcaez";

// Alias compatible con la estructura actual del proyecto
const SUPABASE_KEY = SUPABASE_ANON_KEY;
const SUPABASE_REST_URL = SUPABASE_URL . "/rest/v1";

// Mantener compatibilidad con la conexión previa si se reutiliza en algún punto
const HOST = "db.uxzthiwwpxurhlcbecwh.supabase.co";
const PORT = "5432";
const DB   = "postgres";
const USER = "postgres";
const PASS = "1234";

// Definición antigua, solo por compatibilidad hacia atrás
const SGBD = "pgsql:host=" . HOST . ";port=" . PORT . ";dbname=" . DB . ";sslmode=require";
// Constantes de encriptación requeridas por mainModel
const METHOD = "AES-256-CBC";
const SECRET_KEY = '$CEPROCA@2020';
const SECRET_IV = '037970';
