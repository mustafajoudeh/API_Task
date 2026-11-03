<?php
declare(strict_types=1);

final class User
{
    public function __construct(private PDO $pdo) {}
    public function GetAllUsers(): array { return $this->pdo->query('SELECT id,fullName,username,email FROM `user` ORDER BY id DESC')->fetchAll(); }
    public function GetUser(int $id): ?array { $s=$this->pdo->prepare('SELECT id,fullName,username,email FROM `user` WHERE id=:id');$s->execute(['id'=>$id]);$r=$s->fetch();return $r?:null; }
    private function ensureUnique(string $username,string $email,?int $excludeId=null): void
    {
        $sql='SELECT id FROM `user` WHERE (username=:username OR email=:email)';$p=['username'=>trim($username),'email'=>trim($email)];if($excludeId!==null){$sql.=' AND id<>:id';$p['id']=$excludeId;}$sql.=' LIMIT 1';
        $s=$this->pdo->prepare($sql);$s->execute($p);if($s->fetch())throw new DomainException('Username or email is already in use.');
    }
    public function CreateUser(string $fullName,string $username,string $email,string $password): int
    {
        $this->validate($fullName,$username,$email);if(strlen($password)<8)throw new InvalidArgumentException('Password must be at least 8 characters.');$this->ensureUnique($username,$email);
        $s=$this->pdo->prepare('INSERT INTO `user` (fullName,username,password,email) VALUES (:name,:username,:password,:email)');$s->execute(['name'=>trim($fullName),'username'=>trim($username),'password'=>password_hash($password,PASSWORD_DEFAULT),'email'=>trim($email)]);return (int)$this->pdo->lastInsertId();
    }
    public function UpdateUser(int $id,string $fullName,string $username,string $email,?string $password=null): bool
    {
        if(!$this->GetUser($id))return false;$this->validate($fullName,$username,$email);$this->ensureUnique($username,$email,$id);
        if($password!==null && $password!==''){if(strlen($password)<8)throw new InvalidArgumentException('Password must be at least 8 characters.');$sql='UPDATE `user` SET fullName=:name,username=:username,email=:email,password=:password WHERE id=:id';$p=['name'=>trim($fullName),'username'=>trim($username),'email'=>trim($email),'password'=>password_hash($password,PASSWORD_DEFAULT),'id'=>$id];}
        else{$sql='UPDATE `user` SET fullName=:name,username=:username,email=:email WHERE id=:id';$p=['name'=>trim($fullName),'username'=>trim($username),'email'=>trim($email),'id'=>$id];}
        $s=$this->pdo->prepare($sql);$s->execute($p);return true;
    }
    public function DeleteUser(int $id): bool { $s=$this->pdo->prepare('DELETE FROM `user` WHERE id=:id');$s->execute(['id'=>$id]);return $s->rowCount()>0; }
    private function validate(string $name,string $username,string $email): void
    {
        if(trim($name)===''||trim($username)===''||trim($email)==='')throw new InvalidArgumentException('Full name, username and email are required.');if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Enter a valid email address.');
    }
}