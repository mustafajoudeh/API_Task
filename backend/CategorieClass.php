<?php
declare(strict_types=1);

final class Categorie
{
    public function __construct(private PDO $pdo) {}

    public function GetAllCategories(): array
    {
        return $this->pdo->query('SELECT CategorieID, CategorieName FROM categories ORDER BY CategorieID DESC')->fetchAll();
    }
    public function GetCategorieById(int $id): ?array
    {
        $s=$this->pdo->prepare('SELECT CategorieID, CategorieName FROM categories WHERE CategorieID=:id');
        $s->execute(['id'=>$id]); $row=$s->fetch(); return $row ?: null;
    }
    public function CategorieNameExists(string $name, ?int $excludeId=null): bool
    {
        $sql='SELECT COUNT(*) FROM categories WHERE LOWER(CategorieName)=LOWER(:name)'; $p=['name'=>trim($name)];
        if($excludeId!==null){$sql.=' AND CategorieID<>:id';$p['id']=$excludeId;}
        $s=$this->pdo->prepare($sql);$s->execute($p);return (int)$s->fetchColumn()>0;
    }
    public function CreateCategorie(string $name): int
    {
        $name=trim($name); if($name==='') throw new InvalidArgumentException('Category name is required.');
        if($this->CategorieNameExists($name)) throw new DomainException('A category with this name already exists.');
        $s=$this->pdo->prepare('INSERT INTO categories (CategorieName) VALUES (:name)');$s->execute(['name'=>$name]);return (int)$this->pdo->lastInsertId();
    }
    public function UpdateCategorie(int $id,string $name): bool
    {
        $name=trim($name);if($name==='')throw new InvalidArgumentException('Category name is required.');
        if(!$this->GetCategorieById($id))return false;
        if($this->CategorieNameExists($name,$id))throw new DomainException('A category with this name already exists.');
        $s=$this->pdo->prepare('UPDATE categories SET CategorieName=:name WHERE CategorieID=:id');$s->execute(['name'=>$name,'id'=>$id]);return true;
    }
    public function HasProducts(int $id): bool
    {
        $s=$this->pdo->prepare('SELECT COUNT(*) FROM product WHERE CategorieID=:id');$s->execute(['id'=>$id]);return (int)$s->fetchColumn()>0;
    }
    public function DeleteCategorie(int $id): bool
    {
        if(!$this->GetCategorieById($id))return false;
        if($this->HasProducts($id))throw new DomainException('This category is assigned to one or more products. Reassign or delete those products first.');
        $s=$this->pdo->prepare('DELETE FROM categories WHERE CategorieID=:id');$s->execute(['id'=>$id]);return $s->rowCount()>0;
    }
}