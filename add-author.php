<?php  
session_start();

# If the admin is logged in
if (isset($_SESSION['user_id']) &&
    isset($_SESSION['user_email'])) {
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Author</title>
	<script>(function(){var t=localStorage.getItem("theme")||"light";document.documentElement.setAttribute("data-theme",t);})();</script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
	<style>
		:root{--bg-primary:#ffffff;--bg-navbar:#f8f9fa;--bg-card:#ffffff;--text-primary:#212529;--text-secondary:#6c757d;--text-muted:#6c757d;--border-color:#dee2e6;--shadow:rgba(0,0,0,0.08);--toggle-bg:#e2e8f0;--input-bg:#ffffff;--input-border:#ced4da;}
		[data-theme="dark"]{--bg-primary:#0f1117;--bg-navbar:#13161f;--bg-card:#1e2130;--text-primary:#e8eaf0;--text-secondary:#9aa3b2;--text-muted:#6b7280;--border-color:#2d3148;--shadow:rgba(0,0,0,0.4);--toggle-bg:#3b4263;--input-bg:#1e2130;--input-border:#2d3148;}
		*,*::before,*::after{box-sizing:border-box;transition:background-color 0.3s ease,color 0.3s ease,border-color 0.3s ease;}
		body{background-color:var(--bg-primary)!important;color:var(--text-primary)!important;}
		.navbar{background-color:var(--bg-navbar)!important;border-bottom:1px solid var(--border-color);}
		.navbar-brand{color:var(--text-primary)!important;font-weight:700;}
		.nav-link{color:var(--text-secondary)!important;}
		.nav-link:hover,.nav-link.active{color:var(--text-primary)!important;}
		.form-control{background-color:var(--input-bg)!important;border-color:var(--input-border)!important;color:var(--text-primary)!important;}
		.form-control::placeholder{color:var(--text-muted)!important;}
		.shadow{background-color:var(--bg-card)!important;border:1px solid var(--border-color);box-shadow:0 4px 16px var(--shadow)!important;}
		.form-label{color:var(--text-secondary);}
		.theme-toggle-btn{display:flex;align-items:center;gap:8px;background:var(--toggle-bg);border:none;border-radius:50px;padding:6px 14px 6px 10px;cursor:pointer;font-size:0.82rem;font-weight:600;color:var(--text-primary);transition:all 0.3s ease;white-space:nowrap;}
		.theme-toggle-btn:hover{transform:scale(1.04);}
	</style>
</head>
<body>
	<div class="container">
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
		  <div class="container-fluid">
		    <a class="navbar-brand" href="admin.php">Admin</a>
		    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		      <span class="navbar-toggler-icon"></span>
		    </button>
		    <div class="collapse navbar-collapse" 
		         id="navbarSupportedContent">
		      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
		        <li class="nav-item">
		          <a class="nav-link" 
		             aria-current="page" 
		             href="index.php">Store</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link" 
		             href="add-book.php">Add Book</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link" 
		             href="add-category.php">Add Category</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link active" 
		             href="add-author.php">Add Author</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link" 
		             href="logout.php">Logout</a>
		        </li>
		      </ul>
		      <button class="theme-toggle-btn ms-3" id="themeToggle" onclick="toggleTheme()" title="Toggle dark/light mode">
		        <span id="toggleIcon">🌙</span>
		        <span id="themeLabel">Dark Mode</span>
		      </button>
		    </div>
		  </div>
		</nav>
     <form action="php/add-author.php"
           method="post" 
           class="shadow p-4 rounded mt-5"
           style="width: 90%; max-width: 50rem;">

     	<h1 class="text-center pb-5 display-4 fs-3">
     		Add New Author
     	</h1>
     	<?php if (isset($_GET['error'])) { ?>
          <div class="alert alert-danger" role="alert">
			  <?=htmlspecialchars($_GET['error']); ?>
		  </div>
		<?php } ?>
		<?php if (isset($_GET['success'])) { ?>
          <div class="alert alert-success" role="alert">
			  <?=htmlspecialchars($_GET['success']); ?>
		  </div>
		<?php } ?>
     	<div class="mb-3">
		    <label class="form-label">
		           	Author Name
		           </label>
		    <input type="text" 
		           class="form-control" 
		           name="author_name">
		</div>

	    <input type="submit" 
	            class="btn btn-primary">
	            
     </form>
	</div>
	<script>
		function applyTheme(t){document.documentElement.setAttribute("data-theme",t);localStorage.setItem("theme",t);var i=document.getElementById("toggleIcon");var l=document.getElementById("themeLabel");if(t==="dark"){i.textContent="☀️";l.textContent="Light Mode";}else{i.textContent="🌙";l.textContent="Dark Mode";}}
		function toggleTheme(){applyTheme(document.documentElement.getAttribute("data-theme")==="dark"?"light":"dark");}
		applyTheme(localStorage.getItem("theme")||"light");
	</script>
</body>
</html>

<?php }else{
  header("Location: login.php");
  exit;
} ?>