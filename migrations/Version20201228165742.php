<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201228165742 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {

        //Tabla factura
        $this->addSql('ALTER TABLE factura ADD CONSTRAINT FK_id_cliente_factura FOREIGN KEY (id_cliente) REFERENCES usuario (id) ON DELETE CASCADE ON UPDATE CASCADE');


        //Tabla valoracion
        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_vendedor_valoracion FOREIGN KEY (id_vendedor) REFERENCES vendedor (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_cliente_valoracion FOREIGN KEY (id_cliente) REFERENCES usuario (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_articulo_valoracion FOREIGN KEY (id_articulo) REFERENCES articulo (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_detalle_val FOREIGN KEY (numero_detalle) REFERENCES detalle (id) ON DELETE CASCADE ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_id_factura_valoracion FOREIGN KEY (numero_factura) REFERENCES factura (id) ON DELETE CASCADE ON UPDATE CASCADE');




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

