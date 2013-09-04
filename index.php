<?php
/* By Oros
 * 2013-08-31
 * Licence Public Domaine
 */
$upload_folder="./upload/";
if(!file_exists($upload_folder)){
	@mkdir($upload_folder) or die("Need to create $upload_folder with writing permission !");
}
if(!file_exists("./upload/.htaccess")){
	file_put_contents($upload_folder.".htaccess", "Options -ExecCGI -Indexes
RemoveHandler .php .phtml .php3 .php4 .php5 .html .htm .js
RemoveType .php .phtml .php3 .php4 .php5 .html .htm .js
php_flag engine off
AddType text/plain .php .phtml .php3 .php4 .php5 .html .htm .js");
	file_put_contents($upload_folder."index.html","");
}
if(!empty($_GET)){
	header('content-type: application/json');
	if(!empty($_FILES)){
		$r=array();
		foreach ($_FILES as $file) {
			$name=str_replace(array("..", "/", "\\", "\n", "\r", "\0"), "_", $file['name']);
			if(!in_array($name, array("", ".", "..", ".htaccess", "index.html", "index.php"))){
$r['e'][0]= var_export ($file, true);
$r['e'][1]=$upload_folder.$name;
				if(move_uploaded_file($file['tmp_name'], $upload_folder.$name)){
					$r['ok'][]=$name;
				}else{
					$r['err'][]=$name;
                        	}
			}else{
				$r['err'][]=$name;
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
		#uploaded{border:10px solid #ccc;min-height:50px;margin:20px auto;background-color:#EEEEEE;}
		progress{width:100%;}
		progress:after{content:'%';}
		progress[value]{-webkit-appearance:none;-moz-appearance:none;appearance:none;border:none;background-color:#eee;border-radius:2px;box-shadow:0 2px 5px rgba(0,0,0,0.25) inset;}
		.fail{background:#c00;padding:2px;color:#fff;}
		.hidden{display:none !important;}
		#logo_drop{position:absolute;width:100%;text-align:center;top:0px;margin-top:0px;margin-bottom:0px;font-size:15em;z-index:-1;}
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
			<p id="upload" class="hidden"><label>Drag & drop not supported <span class="aah">😱</span>,<br/>Use this input field to upload file : <input type="file" name="file0"></label></p>
			<p id="filereader" class="hidden">File API & FileReader API not supported <span class="aah">😱</span></p>
			<p id="formdata" class="hidden">XHR2's FormData is not supported <span class="aah">😱</span></p>
			<p id="progress" class="hidden">XHR2's upload progress isn't supported <span class="aah">😱</span></p>
			<p id="IE" class="hidden">Please don't use Internet Explorer. It's a big shit ! <a href="https://www.mozilla.org/">Firefox</a> is better.</p>
			<p>Drag files from your desktop on to the drop zone. Files are upload automatically to this server.</p>
			<div id="progress_contener"></div>
			<fieldset id="dropZone"><legend>Drop zone</legend><p id="logo_drop">⎗</p></fieldset>
			<fieldset id="uploaded"><legend>Files uploaded</legend></fieldset> 
		</article>
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
			fileupload = document.getElementById('upload');

		"filereader formdata progress".split(' ').forEach(function (api) {
			if (tests[api] === false) {
				document.getElementById(api).className = 'fail';
			} else {
				document.getElementById(api).className = 'hidden';
			}
		});

		function preview(file) {
			if (tests.filereader === true && imgType[file.type] === true) {
				var reader = new FileReader();
				reader.onload = function (event) {
					dropZone.insertAdjacentHTML('afterBegin','<img id="f_'+file.name.replace(".","")+'" src="'+event.target.result+'" height="300px" alt=""/>');
				};
				reader.readAsDataURL(file);
			} else {
				dropZone.insertAdjacentHTML('afterBegin', '<p class="file" id="f_'+file.name.replace(".","")+'">' + file.name+'</p>');
			}
		}

		function read(files) {
			var formData = tests.formdata ? new FormData() : null;
			var size_to_up=0;
			for (var i = 0; i < files.length; i++) {
				if(files[i]['size'] > <?php 
					$val = trim(ini_get('post_max_size'));
					$last = strtolower($val[strlen($val)-1]);
					switch($last) { case 'g': $val *= 1024; case 'm': $val *= 1024; case 'k': $val *= 1024;	}
					echo $val; ?>){
					alert('File "'+files[i]['name']+'" is too big ! (><?php echo trim(ini_get('post_max_size')); ?>)');
				}else{
					size_to_up+=files[i]['size']
					if(size_to_up > <?php echo $val; ?>){
						send(tests, formData);
						var formData = tests.formdata ? new FormData() : null;
						size_to_up=0;							
					}
					if (tests.formdata) {
						formData.append('file'+i, files[i]);
					}
					preview(files[i]);
				}
			}
			if(size_to_up>0){
				send(tests, formData);
			}
		}

		function send(tests, formData){
			if (tests.formdata) {
				formData.append('up', 1);
				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'index.php?up');
				var progress_id="progress_"+new Date().getTime();
				document.getElementById('progress_contener').innerHTML += '<progress id="'+progress_id+'" max="100" value="0">0</progress>';
				if (tests.progress) {
					// hack to use the variable progress_id.
					eval("xhr.upload.onprogress = function (event) {"
							+"progress=document.getElementById('"+progress_id+"');"
							+"if (progress!= null && event.lengthComputable) {"
								+"var complete = (event.loaded / event.total * 100 | 0);"
								+"progress.value = progress.innerHTML = complete;"
							+"}"
						+"}");
					eval("xhr.onreadystatechange = function(){"
							+"if(xhr.readyState == 4){"
								+"progress=document.getElementById('"+progress_id+"');"
								+"progress.value = progress.innerHTML = 100;"
								+"var imgs = JSON.parse(xhr.responseText);"
								+"if(imgs != '' && imgs.ok != undefined) {"
									+"for (var i = 0; i < imgs.ok.length; i++) {"
										+"var name = imgs.ok[i].replace('.','');"
										+"if(document.getElementById('f_'+name) != '' && name != '') {"
											+"var link = document.createElement('a');"
											+"link.href='<?php echo $upload_folder; ?>'+imgs.ok[i];"
											+"link.id='a_'+name;"
											+"link.appendChild( document.getElementById('f_'+name));"
											+"document.getElementById('uploaded').insertBefore(link, document.getElementById('uploaded').firstChild);"
										+"}"
									+"}"
								+"}"
								+"if(imgs != '' && imgs.err != undefined) {"
									+"for (var i = 0; i < imgs.err.length; i++) {"
										+"var name = imgs.err[i].replace('.','');"
										+"if(document.getElementById('f_'+name) != '' && name != '') {"
											+"document.getElementById('f_'+name).remove();"
										+"}"
									+"}"
								+"}"
								+"progress.remove();"
							+"}"
						+"};");
				}
				xhr.send(formData);
			}
		}

		if ('draggable' in document.createElement('span')) {
			dropZone.ondragover = function () { this.className = 'hover'; return false; };
			dropZone.ondragend = function () { this.className = ''; return false; };
			dropZone.ondrop = function (e) {
				this.className = '';
				e.preventDefault();
				read(e.dataTransfer.files);
			}
		} else {
			fileupload.className = 'fail';
			fileupload.querySelector('input').onchange = function () { read(this.files); };
		}
		//]]>
	</script>
</body>
</html><?php } ?>
