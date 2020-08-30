<?php
require_once ABSPATH . "vendor/autoload.php";
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

function getLastPostDate(){
	return file("parser.pr");
}

function setLastPostDate($date){
	$f = fopen("parser.pr","w+t");
	$file = fwrite($f, $date);
	if($file){
		fclose($f);
		return true;
	}else{
		fclose($f);
		return false;
	}
}



add_filter( 'cron_schedules', 'cron_add_two_hour' );
function cron_add_two_hour( $schedules ) {
	$schedules['two_h'] = array(
		'interval' => 7220,
		'display' => 'Раз в 2 часа'
	);
	return $schedules;
}

// регистрируем событие
add_action( 'init', 'my_activation' );
function my_activation() {
	if ( ! wp_next_scheduled( 'my_two_hour_event' ) ) {
		wp_schedule_event( time(), 'two_h', 'my_two_hour_event');
	}
}
// добавляем функцию к указанному хуку
add_action( 'my_two_hour_event', 'do_every_two_hour' );
function do_every_two_hour() {
	$d2 = parse_rss_ent("https://news.yahoo.com/rss/");
	$d1 = parse_rss_fin("https://finance.yahoo.com/rss/");
	setLastPostDate($d1 . "\n" . $d2);
}



function parse_rss_fin($url) {
	$rss     = simplexml_load_file( $url );
	$oldDate = strtotime( getLastPostDate()[0] );

	$tmpDate = "";

	foreach ( $rss->channel->item as $items ) {
		$date = strtotime( $items->pubDate );
		if ( $tmpDate == "" ) {
			$tmpDate = $items->pubDate;
		}
		if ( $date > $oldDate && isset( $items->description ) ) {
			//добавлаем пост в базу
			if ( strtotime( $tmpDate ) < strtotime( $items->pubDate ) ) {
				$tmpDate = $items->pubDate;
			}
			$post_id = wp_insert_post( array(
				'post_title'    => $items->title,
				'post_content'  => $items->description,
				'post_author'   => 1,
				'post_status'   => 'publish',
				'post_category' => array( get_category_by_slug( 'news_finance' )->cat_ID ),
				'post_date_gmt' => $items->pubDate
			) );
			//html с описания поста и достаем ссылку картинки
			$doc = new DOMDocument();
			$doc->loadHTML( $items->description );
			$doc->saveHTML();
			$dom = phpQuery::newDocument( $doc );
			if ( pq( $dom->find( "img" ) ) && $post_id != 0 ) {
				$pq  = pq( $dom->find( "img" ) );
				$url = $pq->attr( 'src' );

				// Загрузим файл
				$tmp = download_url( $url );

				// загружаем файл
				$id = media_handle_sideload( array(
					'name'     => basename( $url ) . ".jpg", // ex: wp-header-logo.png
					'tmp_name' => $tmp,
					'error'    => 0,
					'size'     => filesize( $tmp ),
				), $post_id );

				// если ошибка
				if ( is_wp_error( $id ) ) {

					echo $id->get_error_messages();
				}

				// удалим временный файл
				@unlink( $tmp );
				set_post_thumbnail( $post_id, $id );
			}
			phpQuery::unloadDocuments();


		}
	}
	return $tmpDate;
}



function parse_rss_ent($url) {
	$rss     = simplexml_load_file( $url );
	$oldDate = strtotime( getLastPostDate()[1] );

	$tmpDate = "";

	foreach ( $rss->channel->item as $items ) {
		$date = strtotime( $items->pubDate );
		if ( $tmpDate == "" ) {
			$tmpDate = $items->pubDate;
		}
		//print_r ($date);

		if ( $date > $oldDate && isset( $items->description ) ) {
			//добавлаем пост в базу
			if ( strtotime( $tmpDate ) < strtotime( $items->pubDate ) ) {
				$tmpDate = $items->pubDate;
			}
			$post_id = wp_insert_post( array(
				'post_title'    => $items->title,
				'post_content'  => $items->description,
				'post_author'   => 1,
				'post_status'   => 'publish',
				'post_category' => array( get_category_by_slug( 'news_entertainment' )->cat_ID ),
				'post_date_gmt' => $items->pubDate
			) );
			//html с описания поста и достаем ссылку картинки
			$doc = new DOMDocument();
			$doc->loadHTML( $items->description );
			$dom = phpQuery::newDocument( $doc );
			if ( pq( $dom->find( "img" ) ) && $post_id != 0 ) {
				$pq  = pq( $dom->find( "img" ) );
				$url = $pq->attr( 'src' );


// Загрузим файл
				$tmp = download_url( $url );
				echo $tmp;
// Установим данные файла
				//print_r($file_array . "<br />");

// загружаем файл
				$id = media_handle_sideload( array(
					'name'     => basename( $url ) . ".jpg", // ex: wp-header-logo.png
					'tmp_name' => $tmp,
					'error'    => 0,
					'size'     => filesize( $tmp ),
				), $post_id );

// если ошибка
				if ( is_wp_error( $id ) ) {

					echo $id->get_error_messages();
				}

// удалим временный файл
				@unlink( $tmp );
				set_post_thumbnail( $post_id, $id );
			}
			phpQuery::unloadDocuments();

		}
	}
	return $tmpDate;
}
