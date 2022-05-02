
<div class="layer-left">
	<details open>
		<summary><b>Выбрать товары по категории:</b></summary>
		<?php 
			$all_categories_query = pg_query($conn, "SELECT * FROM categories");
			while ($category = pg_fetch_object($all_categories_query)) : ?>
			<p><?= $category->category_name; ?>:</p>
			<ul>
				<?php 
					$subcategory_query = pg_query_params($conn, 'SELECT * FROM subcategories WHERE category_id = $1', Array($category->id));
					while ($subcategory = pg_fetch_object($subcategory_query)) :
				?>
					<li><a href="index.php?category=<?= $subcategory->subcategory_name; ?>"><?= $subcategory->subcategory_name; ?></a></li>
				<?php endwhile ?>
			</ul>
		<?php endwhile ?>
	</details>
</div>

<?php if (isset($moderator)) : ?>
	<div class="layer-right">
		<details>
			<summary><b>Информация о типах характеристик:</b></summary>
			<p>Узнать о всех типах характеристик <a href="property_types_info.php">здесь</a>.</p>
			<?php 
				$all_categories_query = pg_query($conn, "SELECT * FROM categories");
				while ($category = pg_fetch_object($all_categories_query)) : ?>
				<p><?= $category->category_name; ?>:</p>
				<ul>
					<?php 
						$subcategory_query = pg_query_params($conn, 'SELECT * FROM subcategories WHERE category_id = $1', Array($category->id));
						while ($subcategory = pg_fetch_object($subcategory_query)) :
					?>
						<li><a href="property_types_info.php?category=<?= $subcategory->subcategory_name; ?>"><?= $subcategory->subcategory_name; ?></a></li>
					<?php endwhile ?>
				</ul>
			<?php endwhile ?>
		</details>
	</div>

	<div class="layer-nav">
		<div class="form-group-nav">
			<form action="index.php" method="post">
				<button class="btn-nav" name="do_add_product" type="submit">Добавить товар</button>
			</form>
			<!-- <form action="index.php" method="post">
				<button class="btn-nav" name="do_edit_products" type="submit">Редактировать товары</button>
			</form> -->
		</div>
	</div>

	<script type="text/javascript">
		const apply_height_detail = function(div, heightClose, heightOpen) {
			const $details = document.querySelector(div + ' details')
			if ($details) $details.onclick = function() {
				const $div = document.querySelector(div)
				if ($div) {
					if ($details.open) {
						$div.style.height = heightClose;
					} else {
						$div.style.height = heightOpen;
					}
				}
			}
		}

		apply_height_detail('.layer-right', '50px', '350px')
		apply_height_detail('.layer-left', '50px', '500px')
	</script>
<?php endif ?>