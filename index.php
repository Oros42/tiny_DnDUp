<?php
/* By Oros
 * 2013-08-31
 * update : 2016-08-05
 * Licence Public Domaine
 */

if(is_file('config.php')){
	include 'config.php';
}else{
	/* Default config file */
	@file_put_contents('config.php', <<<EOF
<?php
// Default path where you upload files
\$upload_folder="./upload/";

// You can use multi-folders for uploading your files with :
// \$upload_folders=array(URL_KEY1=>PATH1, URL_KEY2=>PATH2,...);
// Example :
// \$upload_folders=array(
//     "bob"=>"./photo_bob/", // URL : http://.../tiny_DnDUp/?f=bob
//     "alice"=>"./photo_alice/" // URL : http://.../tiny_DnDUp/?f=alice
// );
\$upload_folders=array("upload"=>"\$upload_folder");

// Contents of the default htaccess for upload_folder
\$default_htaccess="Options -ExecCGI
# -Indexes
RemoveHandler .php .phtml .php3 .php4 .php5 .html .htm .js
RemoveType .php .phtml .php3 .php4 .php5 .html .htm .js
php_flag engine off
AddType text/plain .php .phtml .php3 .php4 .php5 .html .htm .js";

// HTML contents of the default index.html for upload_folder.
// If empty, then it doesn't create index.html.
\$default_index="";

// Height of preview pictures
\$preview_height="400px";

// Max size for a file
\$files_max_size=ini_get('upload_max_filesize');
// In your PHP conf, you should have upload_max_filesize > post_max_size !
// Example of value :
// \$files_max_size="2M";
// \$files_max_size="1G";

\$not_allowed_chars=array("..", "/", "\\\\", "\\n", "\\r", "\\0", "<", ">");
\$not_allowed_files=array("", ".", "..", ".htaccess", "index.html", "index.php");

// https://www.iana.org/assignments/media-types/media-types.xhtml
\$allowed_file_types=array('image/png', 'image/jpeg', 'image/gif');
//\$allowed_file_types=null; // == allow all files
?>
EOF
	) or die("Can't create config.php (please check folder permissions)");
	echo "Setup done. Now you can edit config.php and reload this page.";
	exit();
}

$files_max_size_val = trim($files_max_size);
$last = strtolower($files_max_size_val[strlen($files_max_size_val)-1]);
$files_max_size_val=(int)$files_max_size_val;
switch($last) { case 'g': $files_max_size_val *= 1024; case 'm': $files_max_size_val *= 1024; case 'k': $files_max_size_val *= 1024; }

$folder_key="";
if(!empty($_GET) && !empty($_GET['f'])){
	if(isset($upload_folders[$_GET['f']])){
		$upload_folder=$upload_folders[$_GET['f']];
		$folder_key="&f=".$_GET['f'];
	}
}

if(!file_exists($upload_folder)){
	@mkdir($upload_folder) or die("Need to create $upload_folder with writing permission !");
}
if(!empty($default_htaccess) && !file_exists($upload_folder.".htaccess")){
	file_put_contents($upload_folder.".htaccess", $default_htaccess);
}
if(!empty($default_index) && !file_exists($upload_folder."index.html")){
	file_put_contents($upload_folder."index.html",$default_index);
}
if(!empty($_GET) && isset($_GET['up'])){
	header('content-type: application/json');
	if(!empty($_FILES)){
		$r=array();
		foreach ($_FILES as $file) {
			$name=$file['name'];
			foreach ($not_allowed_chars as $char) {
				if(strpos(strtolower($name), $char)!==false){
					$r['err'][]="File name not allowed!";
					echo json_encode($r);
					exit();
				}
			}
			if(empty($allowed_file_types) || in_array(strtolower($file['type']), $allowed_file_types)){
				if(!in_array(strtolower($name), $not_allowed_files)){
					if(move_uploaded_file($file['tmp_name'], $upload_folder.$name)){
						$r['ok'][]=$name;
					}else{
						$r['err'][]="0_o for $name";
					}
				}else{
					$r['err'][]="File $name not allowed!";
				}
			}else{
				$r['err'][]="Bad file type for $name";
			}
		}
		echo json_encode($r);
	}else{
		echo json_encode(array("error"));
	}
}else{
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=620">
	<title>Drag and drop for automatic upload</title>
	<style>
		#dropZone{position:relative;top:0px;border:10px dashed #ccc;min-height:300px;margin:20px auto;background-color:rgba(180,255,180,0.4);}
		#dropZone.hover{border:10px dashed #0c0;}
		#dropZone img{margin:10px auto;}
		.file{margin:0;border:1px solid #C9C9C9;background:-moz-linear-gradient(center top,#F5F5F5 0px,#E9E9E9 100%) repeat scroll 0 0 transparent;border-radius:3px 3px 3px 3px;display:inline-block;padding:100px 20px 0;height:200px;vertical-align:text-bottom;text-align:center;max-width:200px;word-wrap:break-word;}
		a{display:inline;text-decoration:none;}
		a:hover{color:#CC0000;}
		#uploadedset{border:10px solid #ccc;min-height:50px;margin:20px auto;background-color:#EEEEEE;}
		progress{width:100%;}
		progress:after{content:'%';}
		progress[value]{-webkit-appearance:none;-moz-appearance:none;appearance:none;border:none;background-color:#eee;border-radius:2px;box-shadow:0 2px 5px rgba(0,0,0,0.25) inset;}
		.fail{background:#c00;padding:2px;color:#fff;}
		.hidden{display:none !important;}
		#logo_drop{position:absolute;width:100%;text-align:center;top:0px;margin-top:0px;margin-bottom:0px;font-size:15em;z-index:-1;}
		#input_file{position:absolute;width:100%;height:100%;text-align:center;top:0px;margin-top:0px;margin-bottom:0px;opacity:0;cursor:pointer;z-index:100}
		a img{border:none;}
		.aah{font-size:2em;}
	</style>
</head>
<body>
	<section id="wrapper">
		<header>
			<h1>Drag and drop for automatic upload</h1>
		</header>
		<article>
			<noscript><p class="fail">Javascript is blocked <span class="aah">😱</span></p></noscript>
			<p id="upload" class="hidden"><label>Drag & drop not supported <span class="aah">😱</span></label></p>
			<p id="filereader" class="hidden">File API & FileReader API not supported <span class="aah">😱</span></p>
			<p id="formdata" class="hidden">XHR2's FormData is not supported <span class="aah">😱</span></p>
			<p id="progress" class="hidden">XHR2's upload progress isn't supported <span class="aah">😱</span></p>
			<p id="IE" class="hidden">Please don't use Internet Explorer. It's a big shit ! <a href="https://www.mozilla.org/">Firefox</a> is better.</p>
			<p>Drag files from your desktop on to the drop zone. Files are upload automatically to this server.</p>
			<div id="progress_contener"></div>
			<fieldset id="dropZone"><legend>Drop zone</legend><p id="logo_drop">⎗</p><input id="input_file" type="file" name="file0" multiple></fieldset>
			<fieldset id="uploadedset"><div id="uploaded"></div><legend>Files uploaded - <a href="#" onclick="clear_uploaded_list(); return false;">Clear</a> </legend></fieldset> 
		</article>
		<footer>
			<br/><a href="https://github.com/Oros42/tiny_DnDUp">Source code</a> - <a href="#" onclick="show_upload_infos(); return false;" id="upload_infos_btn">Show upload infos</a><br>
			<div id="upload_infos" class="hidden">
				Max size for a file : <?php echo trim($files_max_size); ?><br>
				List of allowed file types :<br>
<?php
			foreach ($allowed_file_types as $type) {
					echo "				$type<br>\n";
			}
?>
			</div>
		</footer>
	</section>

	<script type="text/javascript">
		//<![CDATA[
		if( navigator.appName == "Microsoft Internet Explorer"){ document.getElementById('IE').className="fail";}
		var dropZone = document.getElementById('dropZone'),
			tests = {
				filereader:typeof FileReader != 'undefined',
				formdata:!!window.FormData,
				progress:"upload" in new XMLHttpRequest
			},
			imgType = {
				'image/png':true,
				'image/jpeg':true,
				'image/gif':true
			},
			allowedFileTypes = { <?php
				foreach ($allowed_file_types as $type) {
					echo "'$type':true,";
				} ?> },
			files_id = {};

		"filereader formdata progress".split(' ').forEach(function (api) {
			if (tests[api] === false) {
				document.getElementById(api).className = 'fail';
			} else {
				document.getElementById(api).className = 'hidden';
			}
		});

		function name_to_id(name){
			return "f_"+name.replace(".","").replace(" ","").replace("'","").replace('"',"").replace("<","").replace("/","");
		}

		function preview(file) {
			if(files_id[file.name]){
				// if reupload
				document.getElementById(files_id[file.name]).remove();
			}
			var file_name = name_to_id(file.name);
			if (tests.filereader === true && imgType[file.type.toLowerCase()] === true) {
				var reader = new FileReader();
				reader.onload = function (event) {
					dropZone.insertAdjacentHTML('beforeend','<img id="'+file_name+'" src="'+event.target.result+'" height="<?php echo $preview_height; ?>" alt=""/>');
				};
				reader.readAsDataURL(file);
			} else {
				dropZone.insertAdjacentHTML('beforeend', '<p class="file" id="'+file_name+'">' + file.name+'</p>');
			}
			files_id[file.name] = name_to_id(file.name);
		}

		function read(files) {
			var formData = tests.formdata ? new FormData() : null;
			var size_to_up=0;
			var not_allowed_files={};
			var too_big_files=[];
			for (var i = 0; i < files.length; i++) {
				if (Object.keys(allowedFileTypes).length === 0 || allowedFileTypes[files[i].type] === true) {
					if(files[i]['size'] > <?php echo $files_max_size_val; ?> ){
						too_big_files.push(files[i].name);
					}else{
						size_to_up+=files[i]['size'];
						if(size_to_up > <?php echo $files_max_size_val; ?>){
							send(tests, formData);
							var formData = tests.formdata ? new FormData() : null;
							size_to_up=files[i]['size'];	
						}
						if (tests.formdata) {
							formData.append('file'+i, files[i]);
						}
						preview(files[i]);
					}
				}else{
					not_allowed_files[files[i].name]=files[i].type;
				}
			}
			if(size_to_up>0){
				send(tests, formData);
			}
			if(Object.keys(not_allowed_files).length > 0){
				var msg="Not allowed files :";
				for( k in not_allowed_files){
					msg+="\n"+k+" ("+not_allowed_files[k]+")";
				}
				alert(msg);
			}
			if(too_big_files.length > 0){
				alert("Files who are too big (><?php echo trim($files_max_size); ?>) : "+too_big_files.join(', '));
			}
		}

		var upload = function(progress_id){
			return  function (event) {
				progress=document.getElementById(progress_id);
				if (progress!= null && event.lengthComputable) {
					var complete = (event.loaded / event.total * 100 | 0);
					progress.value = progress.innerHTML = complete;
				}
			};
		}

		function move_f(id_name, name, t){
			t=t-1;
			if(document.getElementById(id_name) != undefined) {
				var link = document.createElement('a');
				link.href='<?php echo $upload_folder; ?>'+name;
				link.id='a_'+id_name;
				link.appendChild( document.getElementById(id_name));
				document.getElementById('uploaded').insertBefore(link, document.getElementById('uploaded').firstChild);
			}else{
				if(t>0){
					setTimeout(function(){move_f(id_name, name,t);}, 1000); // because some times, the preview is not finish to load
				}
			}
		}

		function onreadystatechange(xhr, progress_id){
			return function(){
				if(xhr.readyState == 4){
					progress=document.getElementById(progress_id);
					progress.value = progress.innerHTML = 100;
					var files = JSON.parse(xhr.responseText);
					if(files != '' && files.ok != undefined) {
						for (var i = 0; i < files.ok.length; i++) {
							var id_name = name_to_id(files.ok[i]);
							if(id_name != ''){
								move_f(id_name, files.ok[i],10);
							}
						}
					}
					if(files != '' && files.err != undefined) {
						for (var i = 0; i < files.err.length; i++) {
							var name = name_to_id(files.err[i]);
							if(name != '' && document.getElementById(name) != undefined) {
								document.getElementById(name).remove();
							}
						}
					}
					progress.remove();
				}				
			};
		}

		function send(tests, formData){
			if (tests.formdata) {
				formData.append('up', 1);
				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'index.php?up<?php echo $folder_key; ?>');
				var progress_id="progress_"+new Date().getTime();
				document.getElementById('progress_contener').innerHTML += '<progress id="'+progress_id+'" max="100" value="0">0</progress>';
				if (tests.progress) {
					xhr.upload.onprogress = upload(progress_id);
					xhr.onreadystatechange = onreadystatechange(xhr, progress_id);
				}
				xhr.send(formData);
			}
		}

		function clear_uploaded_list(){
			document.getElementById('uploaded').innerHTML="";
		}

		var upload_infos_is_show=false;
		function show_upload_infos(){
			if(upload_infos_is_show){
				// hidden
				document.getElementById('upload_infos_btn').innerHTML="Show upload infos";
				document.getElementById('upload_infos').className="hidden";
			}else{
				// show
				document.getElementById('upload_infos_btn').innerHTML="Hide upload infos";
				document.getElementById('upload_infos').className="";
			}
			upload_infos_is_show=!upload_infos_is_show;
		}

		if ('draggable' in document.createElement('span')) {
			dropZone.ondragover = function () { this.className = 'hover'; return false; };
			dropZone.ondragend = function () { this.className = ''; return false; };
			dropZone.ondrop = function (e) {
				this.className = '';
				e.preventDefault();
				read(e.dataTransfer.files);
			};
		} else {
			document.getElementById('upload').className = 'fail';
		}
		document.getElementById('input_file').onchange = function () { read(this.files); };
		//]]>
	</script>
</body>
</html><?php } ?>
