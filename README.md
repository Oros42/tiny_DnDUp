tiny Drag and Drop Upload
=========================

Upload easily files by drag and drop in the web page.  

1 PHP file with HTML5 + javascript.  


![screenshot0](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-0.png)  
  
![screenshot1](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-1.png)  
  
![screenshot2](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-2.png)  
  
![screenshot3](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-3.png)  
  
![screenshot4](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-4.png)  
  
![screenshot5](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-5.png)  
  
![screenshot6](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-6.png)  
  
![screenshot7](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-7.png)  
  
![screenshot8](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-8.png)  
  
![screenshot9](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-9.png)  
  
![screenshot10](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-10.png)  
  
![screenshot11](https://raw.github.com/Oros42/tiny_DnDUp/readme/screenshot/Capture-11.png)  



You can use multi-folders for uploading your files.  
Open folder_liste.php and add in array your new folders.  
Exemple :  
```
<?php /* $upload_folders=array(URL_KEY1=>PATH1, URL_KEY2=>PATH2,...); */
/* You can add path here */
$upload_folders=array("bob"=>"./upload1/","alice"=>"./upload2/", "toto"=>"../img_toto/"); ?>
```

Now you can call :  
http://.../tiny_DnDUp/?f=bob  
http://.../tiny_DnDUp/?f=alice  
http://.../tiny_DnDUp/?f=toto  
