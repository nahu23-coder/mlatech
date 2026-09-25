CREATE DATABASE mlatech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mlatech;

CREATE TABLE IF NOT EXISTS users (
    id         CHAR(36)     NOT NULL DEFAULT (UUID()),
    name       VARCHAR(50)  NOT NULL,
    email      VARCHAR(150) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
);

CREATE TABLE IF NOT EXISTS categories (
    id         CHAR(36)     NOT NULL DEFAULT (UUID()),
    name       VARCHAR(50)  NOT NULL,
    icon       VARCHAR(10)  NULL, -- emoji que se muestra en la card (ej: 🧠)
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
);

CREATE TABLE IF NOT EXISTS products (
    id          CHAR(36)      NOT NULL DEFAULT (UUID()),
    category_id CHAR(36)      NOT NULL,
    name        VARCHAR(150)  NOT NULL,
    description TEXT          NULL,
    price       DECIMAL(10,2) NOT NULL,
    stock       INT UNSIGNED  NOT NULL DEFAULT 0,
    image_url   VARCHAR(255)  NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_products_category (category_id),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Mensajes del formulario de contacto / soporte
CREATE TABLE IF NOT EXISTS contact_messages (
    id         CHAR(36)     NOT NULL DEFAULT (UUID()),
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(150) NOT NULL,
    message    TEXT         NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
);

-- Categorías de ejemplo (coinciden con las cards del home)
INSERT INTO categories (name, icon) VALUES
    ('Procesadores', '🧠'),
    ('Placas de video', '🖥️'),
    ('Memorias RAM', '💾'),
    ('Almacenamiento', '🗄️'),
    ('Periféricos', '⌨️'),
    ('Accesorios', '🎧');

-- Catálogo de ejemplo (coincide con lo que se ve en el home y en /productos)
INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES
    ((SELECT id FROM categories WHERE name = 'Procesadores'),
        'Procesador Ryzen 5 5600', '6 núcleos / 12 hilos, socket AM4.', 120000.00, 15, NULL),
    ((SELECT id FROM categories WHERE name = 'Procesadores'),
        'Procesador Intel Core i9-13900K', '24 núcleos, ideal para gaming y producción.', 650000.00, 6, NULL),
    ((SELECT id FROM categories WHERE name = 'Procesadores'),
        'Procesador Ryzen 7 7700X', '8 núcleos / 16 hilos, socket AM5.', 320000.00, 10, NULL),

    ((SELECT id FROM categories WHERE name = 'Placas de video'),
        'Placa de video RTX 4060', '8GB GDDR6, ideal para gaming en 1080p/1440p.', 480000.00, 8, NULL),
    ((SELECT id FROM categories WHERE name = 'Placas de video'),
        'Placa de video RTX 4090', '24GB GDDR6X, tope de gama.', 1850000.00, 3, NULL),
    ((SELECT id FROM categories WHERE name = 'Placas de video'),
        'Placa de video RX 7600', '8GB GDDR6, buena relación precio/rendimiento.', 350000.00, 12, NULL),

    ((SELECT id FROM categories WHERE name = 'Memorias RAM'),
        'Memoria RAM 16GB DDR4', 'Kit de 2x8GB, 3200MHz.', 45000.00, 30, NULL),
    ((SELECT id FROM categories WHERE name = 'Memorias RAM'),
        'Memoria RAM 32GB DDR5', 'Kit de 2x16GB, 6000MHz, con disipador RGB.', 130000.00, 18, NULL),

    ((SELECT id FROM categories WHERE name = 'Almacenamiento'),
        'SSD NVMe 1TB', 'Lectura hasta 3500MB/s.', 85000.00, 25, NULL),
    ((SELECT id FROM categories WHERE name = 'Almacenamiento'),
        'Disco rígido HDD 2TB', '7200RPM, ideal para almacenamiento masivo.', 60000.00, 20, NULL),

    ((SELECT id FROM categories WHERE name = 'Periféricos'),
        'Teclado mecánico RGB', 'Switches rojos, retroiluminación RGB.', 38000.00, 20, NULL),
    ((SELECT id FROM categories WHERE name = 'Periféricos'),
        'Mouse gamer 16000 DPI', 'Sensor óptico de alta precisión, 6 botones.', 22000.00, 35, NULL),
    ((SELECT id FROM categories WHERE name = 'Periféricos'),
        'Monitor 24" 144Hz', 'Full HD, panel IPS, ideal para gaming.', 210000.00, 9, NULL),

    ((SELECT id FROM categories WHERE name = 'Accesorios'),
        'Auriculares gamer 7.1', 'Sonido envolvente, micrófono desmontable.', 55000.00, 22, NULL),
    ((SELECT id FROM categories WHERE name = 'Accesorios'),
        'Mousepad XL', '900x400mm, base antideslizante.', 12000.00, 40, NULL),
    ((SELECT id FROM categories WHERE name = 'Accesorios'),
        'Fuente de alimentación 650W 80+ Bronze', 'Certificada, modular semi.', 75000.00, 14, NULL);
