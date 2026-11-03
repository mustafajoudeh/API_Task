<?php
declare(strict_types=1);

final class Products
{
    public function __construct(private PDO $pdo) {}
    private string $select = 'SELECT p.id, p.CategorieID, c.CategorieName, p.PhotoURL, p.`Product name` AS ProductName, p.Description, p.Price, p.Quantity, p.`Created date` AS CreatedDate FROM product p LEFT JOIN categories c ON c.CategorieID=p.CategorieID';
    public function GetAllProducts(): array { return $this->pdo->query($this->select.' ORDER BY p.id DESC')->fetchAll(); }
    public function GetProductById(int $id): ?array { $s=$this->pdo->prepare($this->select.' WHERE p.id=:id');$s->execute(['id'=>$id]);$r=$s->fetch();return $r?:null; }
    public function CreateProduct(int $categoryId,?string $photo,string $name,?string $description,float $price,int $quantity): int
    {
        $this->validate($categoryId,$name,$price,$quantity);
        $s=$this->pdo->prepare('INSERT INTO product (CategorieID,PhotoURL,`Product name`,Description,Price,Quantity,`Created date`) VALUES (:cat,:photo,:name,:description,:price,:quantity,NOW())');
        $s->execute(['cat'=>$categoryId,'photo'=>$photo,'name'=>trim($name),'description'=>$description,'price'=>$price,'quantity'=>$quantity]);return (int)$this->pdo->lastInsertId();
    }
    public function UpdateProduct(int $id,int $categoryId,?string $photo,string $name,?string $description,float $price,int $quantity): bool
    {
        if(!$this->GetProductById($id))return false;$this->validate($categoryId,$name,$price,$quantity);
        $s=$this->pdo->prepare('UPDATE product SET CategorieID=:cat,PhotoURL=:photo,`Product name`=:name,Description=:description,Price=:price,Quantity=:quantity WHERE id=:id');
        $s->execute(['cat'=>$categoryId,'photo'=>$photo,'name'=>trim($name),'description'=>$description,'price'=>$price,'quantity'=>$quantity,'id'=>$id]);return true;
    }
    public function DeleteProduct(int $id): bool { $s=$this->pdo->prepare('DELETE FROM product WHERE id=:id');$s->execute(['id'=>$id]);return $s->rowCount()>0; }
    private function validate(int $categoryId,string $name,float $price,int $quantity): void
    {
        if(trim($name)==='')throw new InvalidArgumentException('Product name is required.');
        if($price<0)throw new InvalidArgumentException('Price cannot be negative.');if($quantity<0)throw new InvalidArgumentException('Quantity cannot be negative.');
        $s=$this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE CategorieID=:id');$s->execute(['id'=>$categoryId]);if((int)$s->fetchColumn()===0)throw new InvalidArgumentException('Select a valid category.');
    }
}