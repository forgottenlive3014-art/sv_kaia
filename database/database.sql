-- ============================================
-- KAIA_SV - Base de Datos Completa v3
-- Incluye: nueva/usada, wishlist, tallas, info detallada
-- ============================================
DROP DATABASE IF EXISTS kaia_sv;
CREATE DATABASE kaia_sv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kaia_sv;

-- USUARIOS
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente','admin') DEFAULT 'cliente',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- CATEGORIAS
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    descripcion VARCHAR(255)
) ENGINE=InnoDB;

-- PRODUCTOS
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    material VARCHAR(150) NULL,
    cuidados VARCHAR(255) NULL,
    medidas VARCHAR(255) NULL,
    marca VARCHAR(80) DEFAULT 'KAIA_SV',
    origen VARCHAR(80) DEFAULT 'El Salvador',
    categoria_id INT,
    precio DECIMAL(10,2) NOT NULL,
    talla VARCHAR(100),
    stock INT DEFAULT 0,
    imagen VARCHAR(255),
    destacado TINYINT(1) DEFAULT 0,
    condicion ENUM('nueva','usada') DEFAULT 'nueva',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_condicion (condicion),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- STOCK POR TALLA
CREATE TABLE producto_tallas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    talla VARCHAR(10) NOT NULL,
    stock INT DEFAULT 0,
    UNIQUE KEY unico_prod_talla (producto_id, talla),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PEDIDOS
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(30) UNIQUE,
    usuario_id INT NULL,
    nombre_cliente VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(150),
    direccion VARCHAR(255),
    metodo_entrega ENUM('domicilio','punto_encuentro','mensajeria') NOT NULL,
    observaciones TEXT,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('Pendiente','Confirmado','Preparando','En camino','Entregado','Cancelado') DEFAULT 'Pendiente',
    fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- DETALLE PEDIDO
CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    talla VARCHAR(10) DEFAULT 'Única',
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- ENTREGAS
CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    servicio VARCHAR(100),
    estado ENUM('Pendiente','Asignado','En camino','Entregado','Cancelado') DEFAULT 'Pendiente',
    fecha_entrega DATETIME NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PROMOCIONES
CREATE TABLE promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    descuento INT NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('activa','inactiva') DEFAULT 'activa'
) ENGINE=InnoDB;

-- WISHLIST
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    fecha_agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unico_wishlist (usuario_id, producto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ CATEGORÍAS ============
INSERT INTO categorias (nombre, descripcion) VALUES
('Pantalones', 'Pantalones baggy y Y2K'),
('Camisetas', 'Camisetas oversize'),
('Tops', 'Tops Y2K'),
('Gorras', 'Gorras urbanas'),
('Bolsos', 'Bolsos Y2K'),
('Pulseras', 'Pulseras juveniles'),
('Collares', 'Collares modernos'),
('Anillos', 'Anillos Y2K'),
('Llaveros', 'Llaveros creativos'),
('Sudaderas', 'Hoodies y sudaderas oversize'),
('Shorts', 'Shorts urbanos'),
('Cinturones', 'Cinturones Y2K'),
('Gafas', 'Lentes de sol Y2K');

-- ============ PRODUCTOS (con info detallada) ============
INSERT INTO productos 
(nombre, descripcion, material, cuidados, medidas, marca, origen, categoria_id, precio, talla, stock, imagen, destacado, condicion) 
VALUES
('Pantalón Baggy Y2K',
 'Pantalón baggy de corte holgado, inspirado en la estética Y2K. Tela resistente con caída perfecta. Ideal para combinar con crop tops o camisetas oversize. Cintura alta con cordón ajustable.',
 'Denim grueso 100% algodón',
 'Lavar a mano, no usar secadora. Planchar a baja temperatura del revés.',
 'Tiro alto, largo completo, corte holgado',
 'KAIA_SV', 'El Salvador',
 1, 25.00, 'S,M,L,XL', 15, 'pantalon_baggy.jpg', 1, 'nueva'),

('Camiseta Oversize',
 'Camiseta oversize con estampado urbano. Algodón premium de 180 g/m², suave al tacto y transpirable. Corte caído en hombros, perfecta para un look relajado.',
 'Algodón peinado 100% (180 g/m²)',
 'Lavar en frío, voltear al revés antes de lavar. No usar cloro.',
 'Corte oversize, hombros caídos',
 'KAIA_SV', 'El Salvador',
 2, 18.00, 'S,M,L,XL', 20, 'camiseta_oversize.jpg', 1, 'nueva'),

('Top Y2K',
 'Top estilo Y2K con detalles brillantes tipo lentejuelas. Escote cuadrado, tirantes finos y corte ajustado. Perfecto para una noche de fiesta o un look atrevido.',
 'Poliéster con lentejuelas',
 'Lavar a mano con agua fría. No planchar sobre las lentejuelas.',
 'Corte ajustado, tiro corto',
 'KAIA_SV', 'El Salvador',
 3, 15.00, 'XS,S,M,L', 12, 'top_y2k.jpg', 1, 'nueva'),

('Gorra Urbana',
 'Gorra urbana con bordado KAIA_SV al frente. Cierre ajustable trasero, visera curva y estructura clásica de 6 paneles. Un básico del streetwear.',
 'Gabardina de algodón',
 'Lavar a mano. Secar a la sombra para mantener la forma.',
 'Ajustable con hebilla trasera',
 'KAIA_SV', 'El Salvador',
 4, 10.00, 'Única', 25, 'gorra_urbana.jpg', 1, 'nueva'),

('Bolso Y2K',
 'Bolso pequeño estilo Y2K con cadena metálica. Cierre de cremallera, interior forrado y bolsillo interno. Perfecto para llevar lo esencial con actitud.',
 'Vinipiel + cadena metálica',
 'Limpiar con paño húmedo. Evitar roce con superficies ásperas.',
 '22 x 14 x 6 cm',
 'KAIA_SV', 'El Salvador',
 5, 15.00, 'Única', 10, 'bolso_y2k.jpg', 1, 'nueva'),

('Pulsera Y2K',
 'Pulsera con charms estilo Y2K. Acero inoxidable con baño de plata, resistente al óxido. Ajustable, perfecta para stackear con otras pulseras.',
 'Acero inoxidable con baño de plata',
 'Evitar contacto con agua y perfumes para prolongar el brillo.',
 'Ajustable 16-19 cm',
 'KAIA_SV', 'El Salvador',
 6, 5.00, 'Única', 40, 'pulsera.jpg', 0, 'nueva'),

('Collar Estrella',
 'Collar minimalista con dije de estrella. Acero inoxidable con acabado pulido. Cadena con extensión para ajustar a diferentes largos.',
 'Acero inoxidable',
 'Limpiar con paño suave. Guardar en bolsa individual.',
 'Largo 45 cm + extensión 5 cm',
 'KAIA_SV', 'El Salvador',
 7, 7.00, 'Única', 30, 'collar.jpg', 0, 'nueva'),

('Anillo Ajustable Y2K',
 'Anillo ajustable estilo Y2K. Diseño grueso con textura, perfecto para usar solo o combinado. Talla universal ajustable.',
 'Aleación de zinc',
 'Quitar antes de dormir o hacer deporte. Evitar contacto con agua.',
 'Tallas ajustables 5-9',
 'KAIA_SV', 'El Salvador',
 8, 4.00, 'Única', 35, 'anillo.jpg', 0, 'nueva'),

('Llavero KAIA',
 'Llavero creativo KAIA_SV con diseño exclusivo. Incluye mosquetón metálico resistente. Perfecto para decorar tu mochila o llaves.',
 'Acrílico + metal',
 'Evitar tensión excesiva en el aro.',
 '5 cm aprox.',
 'KAIA_SV', 'El Salvador',
 9, 3.00, 'Única', 50, 'llavero.jpg', 0, 'nueva'),

('Hoodie Oversize Gris',
 'Sudadera oversize con capucha, algodón perchado de 320 g/m². Interior suave y abrigador. Bolsillo canguro y puños acanalados.',
 'Algodón perchado 320 g/m²',
 'Lavar del revés, secar al aire. No usar secadora.',
 'Corte oversize, capucha forrada',
 'KAIA_SV', 'El Salvador',
 10, 28.00, 'S,M,L,XL', 14, 'hoodie_gris.jpg', 1, 'nueva'),

('Hoodie KAIA Black',
 'Hoodie negro con logo KAIA bordado al frente. Corte relajado, capucha doble forrada y cordones metálicos. La pieza estrella de la colección.',
 'Algodón perchado 350 g/m²',
 'Lavar en frío. No planchar directamente sobre el bordado.',
 'Corte relajado, unisex',
 'KAIA_SV', 'El Salvador',
 10, 30.00, 'S,M,L,XL', 10, 'hoodie_kaia.jpg', 1, 'nueva'),

('Shorts Cargo Y2K',
 'Short cargo con bolsillos laterales, tela resistente de gabardina. Cintura con elástico y cordón. Perfecto para el calor con estilo urbano.',
 'Gabardina de algodón',
 'Lavar a máquina en frío. Secar a la sombra.',
 'Largo medio, 6 bolsillos',
 'KAIA_SV', 'El Salvador',
 11, 20.00, 'S,M,L', 18, 'shorts_cargo.jpg', 0, 'nueva'),

('Cinturón Cadena',
 'Cinturón metálico tipo cadena, estilo Y2K puro. Eslabones plateados resistentes. Cierre de mosquetón ajustable.',
 'Cadena metálica plateada',
 'Evitar doblar en exceso. Guardar colgado.',
 'Largo ajustable',
 'KAIA_SV', 'El Salvador',
 12, 9.00, 'Única', 22, 'cinturon_cadena.jpg', 0, 'nueva'),

('Gafas Retro Y2K',
 'Lentes de sol con marco grueso estilo retro 2000s. Protección UV400. Diseño unisex que combina con cualquier outfit.',
 'Acetato + lentes UV400',
 'Limpiar con paño de microfibra incluido.',
 'Marco 14 cm, unisex',
 'KAIA_SV', 'El Salvador',
 13, 12.00, 'Única', 20, 'gafas_y2k.jpg', 0, 'nueva'),

('Camiseta Vintage Nirvana',
 'Camiseta de segunda mano, excelente estado, estilo grunge de los 90. Estampado original, tela suave por el uso. Pieza única.',
 'Algodón 100%',
 'Lavar a mano por ser vintage. No usar cloro.',
 'Corte clásico',
 'Vintage', 'Estados Unidos',
 2, 12.00, 'M,L', 3, 'camiseta_vintage.jpg', 1, 'usada'),

('Jeans Mom Vintage',
 'Jeans mom de segunda mano, tiro alto, lavado claro. Corte clásico de los 90, tela gruesa. Excelente estado.',
 'Denim 100% algodón',
 'Lavar poco, a mano. Airear entre usos.',
 'Tiro alto, corte mom',
 'Vintage', 'El Salvador',
 1, 18.00, 'S,M', 4, 'jeans_mom.jpg', 1, 'usada'),

('Chaqueta Denim Usada',
 'Chaqueta de jean vintage, única en su tipo. Talla M. Botones metálicos originales, pequeño desgaste natural que le da carácter.',
 'Denim grueso',
 'Lavar a mano, secar a la sombra.',
 'Corte clásico, botones metálicos',
 'Vintage', 'Estados Unidos',
 10, 22.00, 'M', 2, 'chaqueta_denim.jpg', 0, 'usada'),

('Bolso Tejido Vintage',
 'Bolso tejido artesanal de segunda mano, estilo boho. Material natural, único. Perfecto para un look relajado y alternativo.',
 'Hilo de algodón tejido',
 'Lavar a mano, secar en plano.',
 '25 x 20 cm',
 'Artesanal', 'El Salvador',
 5, 10.00, 'Única', 3, 'bolso_vintage.jpg', 0, 'usada'),

('Top Crochet Usado',
 'Top tejido a mano, segunda mano, en muy buen estado. Estilo boho, único en su tipo. Color neutro fácil de combinar.',
 'Hilo de algodón crochet',
 'Lavar a mano con jabón neutro.',
 'Ajustado, tiro corto',
 'Artesanal', 'El Salvador',
 3, 8.00, 'S,M', 3, 'top_crochet.jpg', 0, 'usada'),

('Sudadera Vintage 90s',
 'Sudadera vintage de los 90, colores retro, única. Tela gruesa y cálida. Pieza de colección.',
 'Algodón + poliéster',
 'Lavar a mano, no usar secadora.',
 'Corte clásico retro',
 'Vintage', 'Estados Unidos',
 10, 20.00, 'L', 2, 'sudadera_vintage.jpg', 0, 'usada');

-- ============ STOCK POR TALLA ============
INSERT INTO producto_tallas (producto_id, talla, stock) VALUES
(1,'S',3),(1,'M',5),(1,'L',4),(1,'XL',3),
(2,'S',4),(2,'M',6),(2,'L',6),(2,'XL',4),
(3,'XS',2),(3,'S',4),(3,'M',4),(3,'L',2),
(4,'Única',25),
(5,'Única',10),
(6,'Única',40),
(7,'Única',30),
(8,'Única',35),
(9,'Única',50),
(10,'S',3),(10,'M',5),(10,'L',4),(10,'XL',2),
(11,'S',2),(11,'M',4),(11,'L',3),(11,'XL',1),
(12,'S',6),(12,'M',7),(12,'L',5),
(13,'Única',22),
(14,'Única',20),
(15,'M',2),(15,'L',1),
(16,'S',2),(16,'M',2),
(17,'M',2),
(18,'Única',3),
(19,'S',2),(19,'M',1),
(20,'L',2);

-- ============ PROMOCIONES ============
INSERT INTO promociones (nombre, descripcion, descuento, fecha_inicio, fecha_fin, estado) VALUES
('Y2K WEEK', '15% de descuento en toda la colección Y2K', 15, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'activa'),
('ENVÍO GRATIS', 'Envío gratis en compras mayores a $40', 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'activa');

-- Acceso inicial: admin@kaia.sv / w4XhqTUI6PpMNZDMEvpBEI26
-- Cambia la contraseña después del primer inicio de sesión.
INSERT INTO usuarios (nombre, correo, password, rol) VALUES
('Administrador KAIA', 'admin@kaia.sv', '$2y$10$OX/Cd6jEzx1D/Yaxz.IpA.oWk25yuDJAECfl5QUjNBQNtY8Lz/BRC', 'admin');