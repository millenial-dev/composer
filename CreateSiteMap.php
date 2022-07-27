<?php
class ArrayLinkException extends Exception { }
class TypeFileException extends Exception { }
class SaveFileException extends Exception { }

class CreateSiteMap{
    private $array_link;
    private $type_file;
    private $url_save;
    private $text_file;
    private $arr_type_file;

    public function __construct($array_link,$type_file,$url_save)
    {
        $this->checkParam($array_link,$type_file,$url_save);

        $this->array_link = $array_link;
        $this->type_file = strtolower($type_file);
        $this->url_save = $url_save;

        if($this->type_file=="xml"){
            $this->createXML();
        }
        if($this->type_file=="csv"){
            $this->createCSV();
        }
        if($this->type_file=="json"){
            $this->createJSON();
        }   
        $this->saveSiteMap();
    }
    private function checkParam($array_link,$type_file,$url_save){
        if($array_link==""){
            throw new ArrayLinkException("Вы указали пустой массив.");
        }
        if(!is_array($array_link)){
            throw new ArrayLinkException("Ошибка в массиве ссылок!");
        }
        //Проверить формат массива
        foreach($array_link as $item){
            if(!isset($item["loc"])||!isset($item["lastmod"])||!isset($item["priority"])||!isset($item["changefreq"]))
            throw new ArrayLinkException("Массив передан в неверном формате!");
        }

        $this->arr_type_file =  array("xml", "csv", "json");
        if($type_file==""){
            throw new TypeFileException("Не указан тип создаваемого файла!");
        }
        if(!in_array(strtolower($type_file), $this->arr_type_file)){
            throw new TypeFileException("Указан не верный тип создаваемого файла!");
        }

        //Проверить корректность адреса для сохранения
        if(!is_dir($url_save)) {
            if(!mkdir($url_save, 0777, true)){
                throw new SaveFileException("Проверьте права на указанную директорию.");
            }
        }
    }
    private function createXML(){
        $this->text_file='<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        foreach($this->array_link as $item){
            $this->text_file.='<url>
            <loc>'.$item["loc"].'</loc>
            <lastmod>'.$item["lastmod"].'</lastmod>
            <priority>'.$item["priority"].'</priority>
            <changefreq>'.$item["changefreq"].'</changefreq>
            </url>
            ';
        }
        $this->text_file.='</urlset>';
    }
    private function createCSV(){
        $this->text_file="loc;lastmod;priority;changefreq\n";
        
        foreach($this->array_link as $item){
            $this->text_file.=$item["loc"].";".$item["lastmod"].";".$item["priority"].";".$item["changefreq"]."\n";
        }
        
    }
    private function createJSON(){
        $this->text_file="[";
        foreach($this->array_link as $item){
            if($this->text_file!="["){
                $this->text_file.=",";
            } 
            $this->text_file.='{
                loc: "'.$item["loc"].'",
                lastmod: "'.$item["lastmod"].'",
                priority: '.$item["priority"].',
                changefreq: "'.$item["changefreq"].'"
            }';
        }
        $this->text_file.="]";
    }
    private function saveSiteMap(){
        $fh = fopen($this->url_save."sitemap.".$this->type_file, 'w');
        if(!$fh){
            throw new SaveFileException("Ошибка создания файла. Проверьте права на директорию.");
        }
        fwrite($fh, $this->text_file);
        fclose($fh);
    }
}


