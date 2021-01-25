<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201228162318 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE articulo (id INT AUTO_INCREMENT NOT NULL, id_vendedor INT NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, stock INT NOT NULL, precio INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalle (id INT AUTO_INCREMENT NOT NULL, id_vendedor INT NOT NULL, numero_factura INT NOT NULL, id_articulo INT NOT NULL, cantidad INT NOT NULL, precio INT NOT NULL, total INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE factura (id INT AUTO_INCREMENT NOT NULL, fecha DATE NOT NULL, importe INT NOT NULL, id_cliente INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE valoracion (id INT AUTO_INCREMENT NOT NULL, id_vendedor INT NOT NULL, id_cliente INT NOT NULL, id_articulo INT NOT NULL, numero_detalle INT NOT NULL, numero_factura INT NOT NULL, valor INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendedor (id INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, numero_ventas INT DEFAULT NULL, importe_ventas INT DEFAULT NULL, valoracion INT DEFAULT NULL, UNIQUE INDEX UNIQ_9797982E7EB2C349 (id_usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        //Tabla articulo
        $this->addSql('ALTER TABLE articulo ADD CONSTRAINT FK_id_vendedor FOREIGN KEY (id_vendedor) REFERENCES vendedor (id) ON DELETE CASCADE ON UPDATE CASCADE');

        //Tabla Detalle
        $this->addSql('ALTER TABLE detalle ADD CONSTRAINT FK_id_vendedor_detalle FOREIGN KEY (id_vendedor) REFERENCES vendedor (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE detalle ADD CONSTRAINT FK_id_factura_detalle FOREIGN KEY (numero_factura) REFERENCES factura (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE detalle ADD CONSTRAINT FK_id_articulo_detalle FOREIGN KEY (id_articulo) REFERENCES articulo (id) ON DELETE CASCADE ON UPDATE CASCADE');


        //Tabla factura
        $this->addSql('ALTER TABLE factura ADD CONSTRAINT FK_id_cliente_factura FOREIGN KEY (id_cliente) REFERENCES usuario (id) ON DELETE CASCADE ON UPDATE CASCADE');


        //Tabla valoracion
        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_vendedor_valoracion FOREIGN KEY (id_vendedor) REFERENCES vendedor (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_cliente_valoracion FOREIGN KEY (id_cliente) REFERENCES usuario (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_articulo_valoracion FOREIGN KEY (id_articulo) REFERENCES articulo (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_detalle_val FOREIGN KEY (numero_detalle) REFERENCES detalle (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_factura_valoracion FOREIGN KEY (numero_factura) REFERENCES factura (id) ON DELETE CASCADE ON UPDATE CASCADE');




        $this->addSql('ALTER TABLE vendedor ADD CONSTRAINT FK_9797982E7EB2C349 FOREIGN KEY (id_usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05DCC04A73E FOREIGN KEY (banco_id) REFERENCES banco (id)');
        $this->addSql('CREATE INDEX IDX_2265B05DCC04A73E ON usuario (banco_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE articulo');
        $this->addSql('DROP TABLE detalle');
        $this->addSql('DROP TABLE factura');
        $this->addSql('DROP TABLE valoracion');
        $this->addSql('DROP TABLE vendedor');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05DCC04A73E');
        $this->addSql('DROP INDEX IDX_2265B05DCC04A73E ON usuario');
    }
}
