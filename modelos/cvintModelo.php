<?php
require_once "mainModel.php";

class cvintModelo extends mainModel {

    protected static function crear_curso_vigente_modelo($idCurso) {
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
            INSERT INTO curso_vigente (id_curso_fk, estado_curso_vigente)
            VALUES (:curso, 1)
        ");
        $sql->bindParam(":curso", $idCurso);

        if ($sql->execute()) {
            return $pdo->lastInsertId();
        } else {
            return false;
        }
    }

    protected static function crear_seccion_A_modelo($idCursoVigente, $idProfesor, $dia, $nombreSeccion = "A") {
        try {

            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                INSERT INTO seccion (id_curso_vigente_fk, ci_profesor, nombre_seccion, dia, archivar_seccion)
                VALUES (:id_curso_vigente, :ci_profesor, :nombre_seccion, :dia, 1)
            ");
            $sql->bindParam(":id_curso_vigente", $idCursoVigente);
            $sql->bindParam(":ci_profesor", $idProfesor);
            $sql->bindParam(":nombre_seccion", $nombreSeccion);
            $sql->bindParam(":dia", $dia);
            return $sql->execute();
        } catch (Exception $e) {
            
            error_log("Error al crear sección '{$nombreSeccion}': " . $e->getMessage());
            return false;
        }
    }

    protected static function crear_periodo_intensivo_modelo($datos) {
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
            INSERT INTO periodo_intensivo (
                id_curso_vigente_fk,
                fecha_inicio_int,
                fecha_fin_int,
                pago_inscripcion_int,
                pago_encuentro_int,
                pago_titulo_int,
                estado_periodo
            ) VALUES (
                :cv,
                :inicio,
                :fin,
                :insc,
                :enc,
                :tit,
                :estado
            )
        ");
        return $sql->execute([
            ':cv' => $datos['id_curso_vigente_fk'],
            ':inicio' => $datos['fecha_inicio_int'],
            ':fin' => $datos['fecha_fin_int'],
            ':insc' => $datos['pago_inscripcion_int'],
            ':enc' => $datos['pago_encuentro_int'],
            ':tit' => $datos['pago_titulo_int'],
            ':estado' => $datos['estado_periodo']

        ]);
    }



    protected static function obtener_datos_cv_intensivo_modelo($idCursoVigente) {
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
            SELECT 
                cv.id_curso_vigente,
                cv.id_curso_fk,
                c.nombre_curso,
                pi.id_periodo_intensivo,
                pi.fecha_inicio_int,
                pi.fecha_fin_int,
                pi.pago_inscripcion_int,
                pi.pago_encuentro_int,
                pi.pago_titulo_int,
                s.ci_profesor,
                s.id_seccion,
                s.dia,
                p.nombre_profesor,
                p.apellido_profesor
            FROM curso_vigente cv
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN periodo_intensivo pi ON pi.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.nombre_seccion = 'A'
            INNER JOIN profesor p ON p.id_profesor = s.ci_profesor
            WHERE cv.id_curso_vigente = :id
        ");
        $sql->execute([':id' => $idCursoVigente]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }


        protected static function actualizar_periodo_intensivo_modelo($datos) {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                UPDATE periodo_intensivo SET
                    fecha_inicio_int = :inicio,
                    fecha_fin_int = :fin,
                    pago_inscripcion_int = :insc,
                    pago_encuentro_int = :enc,
                    pago_titulo_int = :tit,
                    estado_periodo = :estado
                WHERE id_periodo_intensivo = :id
            ");
            return $sql->execute([
                ':inicio' => $datos['fecha_inicio_int'],
                ':fin' => $datos['fecha_fin_int'],
                ':insc' => $datos['pago_inscripcion_int'],
                ':enc' => $datos['pago_encuentro_int'],
                ':tit' => $datos['pago_titulo_int'],
                ':estado' => $datos['estado_periodo'],
                ':id' => $datos['id_periodo_intensivo']
            ]);
        }

        protected static function actualizar_profesor_seccion_modelo($idSeccion, $idProfesor, $dia) {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                UPDATE seccion SET ci_profesor = :profesor, dia = :dia
                WHERE id_seccion = :id
            ");
            return $sql->execute([
                ':profesor' => $idProfesor,
                ':dia' => $dia,
                ':id' => $idSeccion
            ]);
        }





}