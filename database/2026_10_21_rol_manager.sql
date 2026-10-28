-- Agrega el rol "manager" (encargado de un departamento).
-- Un manager solo puede gestionar laboratorios, horarios, reservaciones
-- y reportes del departamento indicado en usuarios.IDDepartamento.
ALTER TABLE usuarios
    MODIFY rol ENUM('invitado', 'maestro', 'administrador', 'manager') NULL;
