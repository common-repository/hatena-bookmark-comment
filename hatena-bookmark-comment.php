<?php
/*
 Plugin Name: Hatena Bookmark Comment
 Plugin URI: http://wordpress.org/extend/plugins/hatena-bookmark-comment/
 Description: Hatena Bookmark Comment
 Version: 0.2
 Author: makoto_kw
 Author URI: http://www.makotokw.com/
 */
/*  Copyright 2010 makoto_kw (email : makoto.kw+wordpress@gmail.com)
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class Hatena_Bookmark_Comment {
	const VERSION = '0.2';
	const BLOGPARTS_URL = 'http://b.hatena.ne.jp/js/bookmark_blogparts.js';
	const JS_LOCALIZE_OBJECT = 'WPHatenaBookmarkComment';

	var $slug;
	var $url;
	var $option_name;
	var $setting_fileds;
	var $deafult_settings;
	var $settings;

	static function init() {
		$p = new Hatena_Bookmark_Comment();
		$p->_init();
	}

	protected function _init()
	{
		$this->slug = end(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));
		$this->url = get_bloginfo('url').'/wp-content/plugins/'.end(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));
		$this->_init_settings();
		add_action('wp_print_scripts', array($this, 'wp_print_scripts') );
	}
	
	function _init_settings()
	{
		$this->option_name = str_replace('-','_',$this->slug).'_settings';
		$this->setting_fileds = array(
			'listPageDisabled'=>array(
				'label'=>'一覧ページで機能を無効にする',
				'type'=>'checkbox',
				'description'=>'固定記事にマッチしないページでブログパーツ機能を無効にします。一覧ページの表示が遅くなる場合にはチェックを入れてください。',
				'defaults'=>'1',
				'section'=>'listpage',
			),
			'commentInsertSelector'=>array(
				'label'=>'HBBlogParts.commentInsertSelector', 
				'type'=>'text',
				'description'=>'はてなブックマークのコメントが挿入される位置の基準となるDOM ElementをCSSセレクタで指定します。配列(CSV)で指定すると、若い要素から順番にDOM Element の取得を試みます。',
				'defaults'=>'div.post',
				'size'=>80,
				'section'=>'listpage',
			),
			'insertPosition'=>array(
				'label'=>'HBBlogParts.insertPosition', 
				'type'=>'select',
				'description'=>'はてなブックマークのコメントを、HBBlogParts.commentInsertSelector で指定したタグより前方か前方のどちらに挿入するかを指定します。それぞれ "before" と "after" の文字列で指定します。',
				'defaults'=>'after',
				'options'=>array('before'=>'前方', 'after'=>'後方'),
				'section'=>'listpage',
			),
			'permalinkSelector'=>array(
				'label'=>'HBBlogParts.permalinkSelector', 
				'type'=>'text',
				'description'=>'ブログのパーマリンクがどの DOM Element に記載されているかをCSSセレクタで指定します。このCSSセレクタによって取得したDOM Element から得た URL をもとに、ブックマークのコメントを取得・表示します。',
				'defaults'=>'div.hatena-bookmark-marker a, div h3 a, h2.title a, h2.entry-title a, .posted a',
				'size'=>80,
				'section'=>'listpage',
			),
			'singlepageCommentInsertSelector'=>array(
				'label'=>'HBBlogParts.commentInsertSelector', 
				'type'=>'text',
				'description'=>'はてなブックマークのコメントが挿入される位置の基準となるDOM ElementをCSSセレクタで指定します。配列(CSV)で指定すると、若い要素から順番にDOM Element の取得を試みます。',
				'defaults'=>'#comments',
				'size'=>80,
				'section'=>'singlepage',
			),
			'singlepageInsertPosition'=>array(
				'label'=>'HBBlogParts.insertPosition', 
				'type'=>'select',
				'description'=>'はてなブックマークのコメントを、HBBlogParts.commentInsertSelector で指定したタグより前方か前方のどちらに挿入するかを指定します。それぞれ "before" と "after" の文字列で指定します。',
				'defaults'=>'before',
				'options'=>array('before'=>'前方', 'after'=>'後方'),
				'section'=>'singlepage',
			),
			'Design'=>array(
				'label'=>'HBBlogParts.Design', 
				'type'=>'text',
				'description'=>"表示される項目と順番をを設定できます。ブックマークのコメントとして表示したいものを、表示順に配列で指定してください。配列の要素は、長さ1の文字列で、それぞれ、ユーザー名('u')、タグ('t')、コメント('c')、日付('d')、はてなスター('s')を意味しています。",
				'defaults'=>'u,t,c,d,s',
				'size'=>20,
				'section'=>'design',
			),
			'useUserCSS'=>array(
				'label'=>'HBBlogParts.useUserCSS', 
				'type'=>'checkbox',
				'description'=>'ユーザーがCSSを指定することで、はてなブックマークのコメント表示について見た目を変える事ができます。こ設定が有効 になっているとコメント表示部分にはてなが提供するCSSが反映されませんので、より自由にユーザーがCSSを設定することができます。',
				'defaults'=>'0',
				'section'=>'design',
			),
			'listPageCommentLimit'=>array(
				'label'=>'HBBlogParts.listPageCommentLimit',
				'type'=>'text',
				'description'=>'固定記事にマッチしないページで表示されるコメントの数の最大値です。これ以上の数のコメントがあった場合は、"他のコメントを表示"というリンクが現れ、リンクをクリックすることで表示されるようになります。デフォルト値は 3 です。',
				'defaults'=>'3',
				'size'=>5,
				'section'=>'listpage',
			),
			'permalinkCommentLimit'=>array(
				'label'=>'HBBlogParts.permalinkCommentLimit', 
				'type'=>'text',
				'description'=>'固定記事にマッチするページで表示されるコメントの数の最大値です。コメントの数がこの値を越えたときの動作は HBBlogParts.listPageCommentLimit と同様です。デフォルト値は 5 です。',
				'defaults'=>'5',
				'size'=>5,
				'section'=>'singlepage',
			),
			'hideNoBookmark'=>array(
				'label'=>'ブックマークがない場合に非表示にする', 
				'type'=>'checkbox',
				'description'=>'ブックマークがない記事の場合にブログパーツを表示しないようにします。',
				'defaults'=>'0',
				'section'=>'design',
			),
		);
		
		$this->default_settings = array();
		foreach ($this->setting_fileds as $key => $field) {
			$this->default_settings[$key] = $field['defaults'];
		}
		//delete_option($this->option_name);
		$this->settings = wp_parse_args((array)get_option($this->option_name), $this->default_settings );
		if (is_admin()) {
			add_action('admin_menu',  array($this, 'admin_menu') );
			add_action('admin_init',  array($this, 'admin_init') );
		}
	}
	
	function admin_menu()
	{
		add_options_page('はてなブックマークコメントの設定', 'はてなブックマークコメントの設定', 'manage_options', $this->slug, array($this,'options_page'));
	}
	
	function admin_init()
	{
		$page = $this->slug;
		register_setting($this->option_name, $this->option_name, array($this,'validate_settings'));
		add_settings_section($page.'_singlepage', '固定記事の設定', array($this, 'add_no_section'), $page);
		add_settings_section($page.'_listpage', '一覧ページの設定', array($this, 'add_no_section'), $page);
		add_settings_section($page.'_design', 'デザインの設定', array($this, 'add_no_section'), $page);
		foreach ($this->setting_fileds as $key => $field) {
			$label = ($field['type']=='checkbox') ? '' : $field['label'];
			add_settings_field(
				$this->option_name.'_'.$key, 
				$label,
				//array($this,'add_settings_field'),
				array($this,'add_settings_field_'.$key),
				$page,
				$page.'_'.$field['section']
				// , array($key, $field) // not work wordpress 2.9.0 #11143
				);
		}
	}
	
	function validate_settings($settings) {
		foreach ($this->setting_fileds as $key => $field) {
			if ($field['type']=='checkbox') {
				$settings[$key] = ($settings[$key] == 'on');
			}
		}
		return $settings;
	}
	
	function add_no_section() {}
	
	function add_singlepage_section() {
		echo '固定記事用の設定項目です。説明は<a href="http://d.hatena.ne.jp/keyword/%A4%CF%A4%C6%A4%CA%A5%D6%A5%C3%A5%AF%A5%DE%A1%BC%A5%AF%A5%B3%A5%E1%A5%F3%A5%C8%C9%BD%BC%A8%A5%D6%A5%ED%A5%B0%A5%D1%A1%BC%A5%C4" target="_blank">こちら</a>にあります。';
	}
	function add_listpage_section() {
		echo '一覧ページ用の設定項目です。説明は<a href="http://d.hatena.ne.jp/keyword/%A4%CF%A4%C6%A4%CA%A5%D6%A5%C3%A5%AF%A5%DE%A1%BC%A5%AF%A5%B3%A5%E1%A5%F3%A5%C8%C9%BD%BC%A8%A5%D6%A5%ED%A5%B0%A5%D1%A1%BC%A5%C4" target="_blank">こちら</a>にあります。';
	}
	function add_design_section() {
		echo '説明は<a href="http://d.hatena.ne.jp/keyword/%A4%CF%A4%C6%A4%CA%A5%D6%A5%C3%A5%AF%A5%DE%A1%BC%A5%AF%A5%B3%A5%E1%A5%F3%A5%C8%C9%BD%BC%A8%A5%D6%A5%ED%A5%B0%A5%D1%A1%BC%A5%C4" target="_blank">こちら</a>にあります。';
	}

	function add_settings_field($key, $field)
	{
		$id = $this->option_name.'_'.$key;
		$name = $this->option_name."[{$key}]";
		$value = $this->settings[$key];
		switch ($field['type']) {
			case 'checkbox':
				echo "<input id='{$id}' name='{$name}' type='checkbox' ".checked(true,$value,false)."/>";
				echo "<label for='{$id}'>".$field['label']."</label>";
				break;
			case 'select':
				echo "<select id='{$id}' name='{$name}' value='{$value}'>";
				foreach ($field['options'] as $option => $name) {
					echo "<option value='{$option}' ".selected($option,$value,false).">{$name}</option>";
				}
				echo "</select>";
				break;
			case 'text':
			default:
				$size = @$field['size'];
				if ($size<=0) $size = 40;
				echo "<input id='{$id}' name='{$name}' size='{$size}' type='text' value='{$value}' />";
				break;
		}
		if (@$field['description']) {
			echo "<p>".$field['description']."</p>";
		}
	}

	function add_settings_field_commentInsertSelector() { $this->add_settings_field('commentInsertSelector', $this->setting_fileds['commentInsertSelector']); }
	function add_settings_field_permalinkSelector() { $this->add_settings_field('permalinkSelector', $this->setting_fileds['permalinkSelector']); }
	function add_settings_field_insertPosition() { $this->add_settings_field('insertPosition', $this->setting_fileds['insertPosition']); }
	function add_settings_field_Design() { $this->add_settings_field('Design', $this->setting_fileds['Design']); }
	function add_settings_field_useUserCSS() { $this->add_settings_field('useUserCSS', $this->setting_fileds['useUserCSS']); }
	function add_settings_field_listPageCommentLimit() { $this->add_settings_field('listPageCommentLimit', $this->setting_fileds['listPageCommentLimit']); }
	function add_settings_field_permalinkCommentLimit() { $this->add_settings_field('permalinkCommentLimit', $this->setting_fileds['permalinkCommentLimit']); }

	function add_settings_field_listPageDisabled() { $this->add_settings_field('listPageDisabled', $this->setting_fileds['listPageDisabled']); }
	function add_settings_field_singlepageCommentInsertSelector() { $this->add_settings_field('singlepageCommentInsertSelector', $this->setting_fileds['singlepageCommentInsertSelector']); }
	function add_settings_field_singlepageInsertPosition() { $this->add_settings_field('singlepageInsertPosition', $this->setting_fileds['singlepageInsertPosition']); }
	function add_settings_field_hideNoBookmark() { $this->add_settings_field('hideNoBookmark', $this->setting_fileds['hideNoBookmark']); }

	function options_page() {
		$page = $this->slug;
		?>
<div class="wrap">
<h2>はてなブックマークコメントの設定</h2>
<form action="options.php" method="post">
<?php settings_fields($this->option_name); ?>
<?php do_settings_sections($page); ?>
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>"/>
</form>
</div>
		<?php
	}
	
	function get_permalink_path_regex()
	{
		if (is_single() || is_page()) {
			$link = parse_url(get_permalink());
			return '^'.preg_quote($link['path']);
		}
		$permalink = get_option('permalink_structure');
		foreach (array(
				'%year%'=>'[\d]+',
				'%monthnum%'=>'[\d]+',
				'%day%'=>'[\d]+',
				'%hour%'=>'[\d]+',
				'%minute%'=>'[\d]+',
				'%second%'=>'[\d]+',
				'%postname%'=>'[\w_-]+',
				'%post_id%'=>'[\w]+',
				'%category%'=>'[\w]+',
				'%author%'=>'[\w_-]+',
				'%pagename%'=>'[\w_-]+',
			) as $src => $dst) {
				$permalink = str_replace($src, $dst, $permalink);
		}
		$permalink = preg_quote($permalink);
		return $permalink;
	}
	
	function can_handle()
	{
		if (is_single() || is_page() || (!$this->settings['listPageDisabled'] && have_posts())) {
			return true;
		}
	}

	function wp_print_scripts()
	{
		if (!$this->can_handle()) return;
		$options = wp_parse_args($this->settings,array(
			'permalinkPathRegexp' => $this->get_permalink_path_regex()
		));
		if (is_single() || is_page()) {
			$options['permalinkURI'] = str_replace('.local','',get_permalink());
			$options['permalinkURI'] = preg_replace('/(%[0-9a-f]{2})/e', "strtoupper('$1');", $options['permalinkURI']) ;
			$options['commentInsertSelector'] = $options['singlepageCommentInsertSelector'];
			$options['insertPosition'] = $options['singlepageInsertPosition'];
		}
		foreach ($this->setting_fileds as $key => $field) {
			if (strpos($field['label'],'HBBlogParts')===false) {
				unset($options[$key]);
			}
		}
		wp_enqueue_script($this->slug.'_blogparts', self::BLOGPARTS_URL, null, self::VERSION);
		//wp_enqueue_script($this->slug.'_blogparts', $this->url.'/bookmark_blogparts.js', null, self::VERSION);
		wp_enqueue_script($this->slug.'_blogparts_patch', $this->url.'/bookmark_blogparts_patch.js', array($this->slug.'_blogparts','jquery'), self::VERSION);
		wp_localize_script($this->slug.'_blogparts_patch', 'WPHatenaBookmarkComment', $options);
	}
}
add_action('init', array('Hatena_Bookmark_Comment', 'init'));