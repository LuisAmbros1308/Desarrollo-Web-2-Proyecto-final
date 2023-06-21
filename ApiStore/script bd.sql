create table servicio(
	id integer not null AUTO_INCREMENT PRIMARY key,
    fechaRegistro date not null,
    horaRegistro time not null,
    idPaciente integer not null,
    idEmpleado integer not null,
    fechaEntrega date not null,
    horaEntrega time not null,
    edadPaciente smallint null,
    total float not null,
    descuento float,
    iva float not null,
    importe float not null,
    observaciones varchar(150)
    #, CONSTRAINT FOREIGN KEY (idEmpleado) REFERENCES Empleado(id) 
);

