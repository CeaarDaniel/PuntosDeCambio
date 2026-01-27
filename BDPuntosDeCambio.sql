USE [ELECTRONICOSDB]


CREATE TABLE [dbo].[roles](
	[idRol] [int] IDENTITY(1,1) NOT NULL,
	[codigoSistema] [nvarchar](50) NOT NULL,
	[codigoRol] [nvarchar](50) NOT NULL,
	[nombreRol] [nvarchar](100) NOT NULL,
	[descripcion] [nvarchar](250) NULL,
	[nivelPermisos] [nvarchar](50) NULL
) 

CREATE TABLE [dbo].[SPC_CERTIFICACIONES](
	[codigo_certificacion] [nvarchar](30) NOT NULL,
	[nombre_certificacion] [nvarchar](150) NOT NULL,
	[tipo_proceso] [nvarchar](50) NULL,
	[descripcion] [nvarchar](max) NULL,
		PRIMARY KEY CLUSTERED 
		(
			[codigo_certificacion] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[SPC_ESTACIONES](
	[id_estacion] [int] IDENTITY(1,1) NOT NULL,
	[nombre_estacion] [nvarchar](150) NOT NULL,
	[descripcion] [nvarchar](max) NULL,
	[requiere_certificacion] [bit] NULL,
	[codigo_certificacion] [nvarchar](30) NULL,
	[posicion_x] [decimal](10, 2) NULL,
	[posicion_y] [decimal](10, 2) NULL,
	[codigo_linea] [nvarchar](20) NULL,
		PRIMARY KEY CLUSTERED 
		(
			[id_estacion] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[SPC_LINEAS](
	[codigo_linea] [nvarchar](20) NOT NULL,
	[nombre_linea] [nvarchar](100) NOT NULL,
	[descripcion] [nvarchar](max) NULL,
	[encargado_supervisor] [nvarchar](100) NULL,
		PRIMARY KEY CLUSTERED 
		(
			[codigo_linea] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[SPC_PERSONAL_CERTIFICACIONES](
	[id_registro] [int] IDENTITY(1,1) NOT NULL,
	[codigo_certificacion] [nvarchar](30) NOT NULL,
	[nomina] [nvarchar](50) NOT NULL,
	[fecha_certificacion] [date] NOT NULL,
	[fecha_vencimiento] [date] NULL,
	[documento] [nvarchar](255) NULL,
	[comentarios] [nvarchar](max) NULL,
	[nivel_ilu] [nvarchar](50) NULL,
		PRIMARY KEY CLUSTERED 
		(
			[id_registro] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[SPC_PERSONAL_ESTACION](
	[id_asignacion] [int] IDENTITY(1,1) NOT NULL,
	[id_estacion] [int] NOT NULL,
	[nomina] [nvarchar](50) NOT NULL,
	[nombre] [varchar](150) NOT NULL,
	[fecha_asignacion] [datetime] NOT NULL,
	[fecha_fin] [datetime] NULL,
	[turno] [nvarchar](5) NULL,
	[comentarios] [nvarchar](max) NULL,
		PRIMARY KEY CLUSTERED 
		(
			[id_asignacion] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[SPC_PERSONAL_NAD](
	[id_registro] [int] IDENTITY(1,1) NOT NULL,
	[nomina] [nvarchar](50) NOT NULL,
	[codigo_linea] [nvarchar](20) NULL,
	[turno] [nvarchar](5) NULL,
	[comentarios] [nvarchar](max) NULL,
	[fechaR] [datetime] NOT NULL,
	[fechaE] [datetime] NULL,
	[eliminado] [nvarchar](5) NOT NULL
) 

CREATE TABLE [dbo].[SPC_PUNTOS_CAMBIO](
	[no_controlCambio] [varchar](50) NOT NULL,
	[fechaHora_inicio] [datetime] NOT NULL,
	[fechaHora_fin] [datetime] NULL,
	[motivo] [nvarchar](max) NOT NULL,
	[tipo_cambio] [nvarchar](20) NOT NULL,
	[codigo_linea] [nvarchar](20) NULL,
	[id_estacion] [int] NULL,
	[estatusPC] [nvarchar](5) NULL,
		PRIMARY KEY CLUSTERED 
		(
			[no_controlCambio] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[SPC_REGISTRO_ASISTENCIA](
	[id_registro] [int] IDENTITY(1,1) NOT NULL,
	[nomina] [nvarchar](50) NOT NULL,
	[nombre] [nvarchar](150) NOT NULL,
	[codigo_linea] [nvarchar](20) NULL,
	[estatus] [nvarchar](30) NULL,
	[id_estacion] [int] NULL,
	[fecha_operacion] [datetime] NULL,
	PRIMARY KEY CLUSTERED 
	(
		[id_registro] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) 

CREATE TABLE [dbo].[usuario_roles](
	[idUR] [int] IDENTITY(1,1) NOT NULL,
	[idRol] [int] NOT NULL,
	[codigoRol] [nvarchar](50) NOT NULL,
	[codigoSistema] [nvarchar](50) NULL,
	[nominaUsuario] [nvarchar](15) NOT NULL
)

CREATE TABLE [dbo].[usuarios](
	[nominaUsuario] [nvarchar](15) NOT NULL,
	[nombre] [nvarchar](100) NOT NULL,
	[passwordHash] [nvarchar](256) NOT NULL,
	[estatus] [nvarchar](10) NOT NULL,
	[fechacreacion] [date] NULL,
	[comentarios] [nvarchar](1000) NULL
) 


SET IDENTITY_INSERT [dbo].[roles] ON 
INSERT [dbo].[roles] ([idRol], [codigoSistema], [codigoRol], [nombreRol], [descripcion], [nivelPermisos]) VALUES (1, N'SRD', N'RRDA', N'adminRD', N'rol admin de registro para registro descansos', N'admin')
SET IDENTITY_INSERT [dbo].[roles] OFF


SET IDENTITY_INSERT [dbo].[SPC_ESTACIONES] ON 
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (2, N'Corte de cable', N'Linea de corte de cable', 0, NULL, CAST(425.00 AS Decimal(10, 2)), CAST(0.00 AS Decimal(10, 2)), N'Xs2Za4$%&#')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (3, N'Desforre de circuito', N'Estación de desforre para la línea de CRV 23', 0, NULL, CAST(25.00 AS Decimal(10, 2)), CAST(0.00 AS Decimal(10, 2)), N'CRV23')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (4, N'Moldeo de uretano Y', N'ESTACION DE PRUEBA', 0, NULL, CAST(20.00 AS Decimal(10, 2)), CAST(200.00 AS Decimal(10, 2)), N'CRV23')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (5, N'PRUEBA', N'prueba', 0, NULL, CAST(212.00 AS Decimal(10, 2)), CAST(0.00 AS Decimal(10, 2)), N'Xs2Za4$%&#')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (6, N'test 3', N'sdf', 0, NULL, CAST(0.00 AS Decimal(10, 2)), CAST(0.00 AS Decimal(10, 2)), N'Xs2Za4$%&#')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (7, N'PRUEBA4', N'null', 0, NULL, CAST(638.00 AS Decimal(10, 2)), CAST(1.00 AS Decimal(10, 2)), N'Xs2Za4$%&#')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (8, N'test', N'test', 0, NULL, CAST(222.00 AS Decimal(10, 2)), CAST(210.00 AS Decimal(10, 2)), N'CRV23')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (9, N'PRUEBA', N'test', 0, NULL, CAST(229.00 AS Decimal(10, 2)), CAST(0.00 AS Decimal(10, 2)), N'CRV23')
INSERT [dbo].[SPC_ESTACIONES] ([id_estacion], [nombre_estacion], [descripcion], [requiere_certificacion], [codigo_certificacion], [posicion_x], [posicion_y], [codigo_linea]) VALUES (10, N'PRUEBA', N'test', 0, NULL, CAST(431.00 AS Decimal(10, 2)), CAST(0.00 AS Decimal(10, 2)), N'CRV23')
SET IDENTITY_INSERT [dbo].[SPC_ESTACIONES] OFF


INSERT [dbo].[SPC_LINEAS] ([codigo_linea], [nombre_linea], [descripcion], [encargado_supervisor]) VALUES (N'CRV23', N'CRV 23', N'LINEA DE CRV 23 DEL AREA DE SENSOR', N'null')
INSERT [dbo].[SPC_LINEAS] ([codigo_linea], [nombre_linea], [descripcion], [encargado_supervisor]) VALUES (N'FORD', N'FORD', N'Línea de Ford', N'null')
INSERT [dbo].[SPC_LINEAS] ([codigo_linea], [nombre_linea], [descripcion], [encargado_supervisor]) VALUES (N'ODYSSEY', N'ODYSSEY', N'LINEA DE ODYSSEY PARA EL AREA DE SENSOR', N'null')
INSERT [dbo].[SPC_LINEAS] ([codigo_linea], [nombre_linea], [descripcion], [encargado_supervisor]) VALUES (N'PILOT', N'PILOT/MDX', N'LINEA DE PILOT O MDX DEL AREA DE SENSOR', N'null')
INSERT [dbo].[SPC_LINEAS] ([codigo_linea], [nombre_linea], [descripcion], [encargado_supervisor]) VALUES (N'Xs2Za4$%&#', N'PRUEBA', N'PRUEBA', N'PRUEBA')


SET IDENTITY_INSERT [dbo].[SPC_PERSONAL_ESTACION] ON 
INSERT [dbo].[SPC_PERSONAL_ESTACION] ([id_asignacion], [id_estacion], [nomina], [nombre], [fecha_asignacion], [fecha_fin], [turno], [comentarios]) VALUES (3, 3, N'11607', N'VEGA CARDENAS, CESAR DANIEL', CAST(N'2026-01-23T00:00:00.000' AS DateTime), NULL, N'1', N'')
INSERT [dbo].[SPC_PERSONAL_ESTACION] ([id_asignacion], [id_estacion], [nomina], [nombre], [fecha_asignacion], [fecha_fin], [turno], [comentarios]) VALUES (4, 8, N'11605', N'FLORES AGUILERA, EFRAIN', CAST(N'2026-01-23T00:00:00.000' AS DateTime), CAST(N'2026-01-26T23:53:39.800' AS DateTime), N'1', N'')
INSERT [dbo].[SPC_PERSONAL_ESTACION] ([id_asignacion], [id_estacion], [nomina], [nombre], [fecha_asignacion], [fecha_fin], [turno], [comentarios]) VALUES (6, 9, N'11608', N'MORENO MENDOZA, DORIAN EDUARDO', CAST(N'2026-01-23T00:00:00.000' AS DateTime), NULL, N'1', N'')
SET IDENTITY_INSERT [dbo].[SPC_PERSONAL_ESTACION] OFF


SET IDENTITY_INSERT [dbo].[SPC_PERSONAL_NAD] ON 
INSERT [dbo].[SPC_PERSONAL_NAD] ([id_registro], [nomina], [codigo_linea], [turno], [comentarios], [fechaR], [fechaE], [eliminado]) VALUES (7, N'11607', N'CRV23', N'1', N'prueba', CAST(N'2026-01-26T11:42:00.000' AS DateTime), NULL, N'0')
INSERT [dbo].[SPC_PERSONAL_NAD] ([id_registro], [nomina], [codigo_linea], [turno], [comentarios], [fechaR], [fechaE], [eliminado]) VALUES (9, N'11609', N'CRV23', N'1', N'', CAST(N'2026-01-26T16:32:00.000' AS DateTime), NULL, N'0')
INSERT [dbo].[SPC_PERSONAL_NAD] ([id_registro], [nomina], [codigo_linea], [turno], [comentarios], [fechaR], [fechaE], [eliminado]) VALUES (8, N'11608', N'CRV23', N'2', N'prueba', CAST(N'2026-01-26T16:28:00.000' AS DateTime), NULL, N'0')
SET IDENTITY_INSERT [dbo].[SPC_PERSONAL_NAD] OFF


SET IDENTITY_INSERT [dbo].[usuario_roles] ON 
INSERT [dbo].[usuario_roles] ([idUR], [idRol], [codigoRol], [codigoSistema], [nominaUsuario]) VALUES (1, 1, N'RRDA', NULL, N'11607')
SET IDENTITY_INSERT [dbo].[usuario_roles] OFF

INSERT [dbo].[usuarios] ([nominaUsuario], [nombre], [passwordHash], [estatus], [fechacreacion], [comentarios]) VALUES (N'11607', N'Cesar Daniel Vega', N'Ma1Fr2Xs3Cv4', N'activo', NULL, N'rol administrador')
SET ANSI_PADDING ON

/****** Object:  Index [UQ__usuarios__EF82F1D6A79F9289]    Script Date: 2026-01-27 08:46:18 a. m. ******/
ALTER TABLE [dbo].[usuarios] ADD UNIQUE NONCLUSTERED 
(
	[nominaUsuario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
ALTER TABLE [dbo].[SPC_REGISTRO_ASISTENCIA] ADD  DEFAULT (getdate()) FOR [fecha_operacion]
GO
ALTER TABLE [dbo].[SPC_ESTACIONES]  WITH CHECK ADD FOREIGN KEY([codigo_linea])
REFERENCES [dbo].[SPC_LINEAS] ([codigo_linea])
GO
ALTER TABLE [dbo].[SPC_ESTACIONES]  WITH CHECK ADD FOREIGN KEY([codigo_certificacion])
REFERENCES [dbo].[SPC_CERTIFICACIONES] ([codigo_certificacion])
GO
ALTER TABLE [dbo].[SPC_PERSONAL_CERTIFICACIONES]  WITH CHECK ADD FOREIGN KEY([codigo_certificacion])
REFERENCES [dbo].[SPC_CERTIFICACIONES] ([codigo_certificacion])
GO
ALTER TABLE [dbo].[SPC_PERSONAL_ESTACION]  WITH CHECK ADD FOREIGN KEY([id_estacion])
REFERENCES [dbo].[SPC_ESTACIONES] ([id_estacion])
GO
ALTER TABLE [dbo].[SPC_PERSONAL_NAD]  WITH CHECK ADD FOREIGN KEY([codigo_linea])
REFERENCES [dbo].[SPC_LINEAS] ([codigo_linea])
GO
ALTER TABLE [dbo].[SPC_PUNTOS_CAMBIO]  WITH CHECK ADD FOREIGN KEY([codigo_linea])
REFERENCES [dbo].[SPC_LINEAS] ([codigo_linea])
GO
ALTER TABLE [dbo].[SPC_PUNTOS_CAMBIO]  WITH CHECK ADD FOREIGN KEY([id_estacion])
REFERENCES [dbo].[SPC_ESTACIONES] ([id_estacion])
GO
ALTER TABLE [dbo].[SPC_REGISTRO_ASISTENCIA]  WITH CHECK ADD FOREIGN KEY([codigo_linea])
REFERENCES [dbo].[SPC_LINEAS] ([codigo_linea])
GO
ALTER TABLE [dbo].[SPC_REGISTRO_ASISTENCIA]  WITH CHECK ADD FOREIGN KEY([id_estacion])
REFERENCES [dbo].[SPC_ESTACIONES] ([id_estacion])
GO