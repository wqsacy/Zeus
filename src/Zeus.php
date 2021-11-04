<?php
	
	namespace Wangqs\Zeus;
	
	class Zeus
	{
		
		// check mobile number
		public static function isValidMobile ( $mobile ) : bool {
			return (bool) preg_match( '/^1[3456789]{1}\d{9}$/' , $mobile );
		}
		
		
		public static function isValidIdNo ( $id ) : bool {
			$id        = strtoupper( $id );
			$regx      = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
			$arr_split = [];
			if ( !preg_match( $regx , $id ) ) {
				return false;
			}
			//检查15位
			if ( 15 == strlen( $id ) ) {
				$regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
				@preg_match( $regx , $id , $arr_split );
				$dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
				if ( !strtotime( $dtm_birth ) ) {
					return false;
				} else {
					return true;
				}
			} else {
				//检查18位
				$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
				@preg_match( $regx , $id , $arr_split );
				$dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
				//检查生日日期是否正确
				if ( !strtotime( $dtm_birth ) ) {
					return false;
				} else {
					//检验18位身份证的校验码是否正确。
					//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
					$arr_int = [
						7 ,
						9 ,
						10 ,
						5 ,
						8 ,
						4 ,
						2 ,
						1 ,
						6 ,
						3 ,
						7 ,
						9 ,
						10 ,
						5 ,
						8 ,
						4 ,
						2
					];
					$arr_ch  = [
						'1' ,
						'0' ,
						'X' ,
						'9' ,
						'8' ,
						'7' ,
						'6' ,
						'5' ,
						'4' ,
						'3' ,
						'2'
					];
					$sign    = 0;
					for ( $i = 0; $i < 17; $i++ ) {
						$b    = (int) $id[$i];
						$w    = $arr_int[$i];
						$sign += $b * $w;
					}
					$n       = $sign % 11;
					$val_num = $arr_ch[$n];
					if ( $val_num != substr( $id , 17 , 1 ) ) {
						return false;
					} else {
						return true;
					}
				}
			}
		}
		
		
		public static function humanNum ( $num ) {
			if ( !$num || !is_numeric( $num ) ) {
				return 0;
			}
			
			if ( $num >= 10000 ) {
				$num = round( $num / 10000 * 100 ) / 100 . ' W';
			} else if ( $num >= 1000 ) {
				$num = round( $num / 1000 * 100 ) / 100 . ' K';
			}
			
			return $num;
		}
		
		public static function humanDate ( $time = null ) : string {
			if ( !ctype_digit( $time ) ) {
				$time = strtotime( $time );
			}
			
			$text = '';
			
			$time      = $time === null || $time > time() ? time() : intval( $time );
			$t         = time() - $time; //时间差 （秒）
			$y         = date( 'Y' , $time ) - date( 'Y' , time() ); //是否跨年
			$today     = time() - strtotime( date( 'Y-m-d' , time() ) );  //今日零时到现在的秒数
			$yesterday = $today + 3600 * 24;  //昨日零时到现在的秒数
			switch ( $t ) {
				case 0:
					$text = '刚刚';
					break;
				case $t < 60 && $t < $today:
					$text = $t . '秒前'; // 一分钟内
					break;
				case $t < 60 * 60 && $t < $today:
					$text = floor( $t / 60 ) . '分钟前'; //一小时内
					break;
				case $t < 60 * 60 * 24 && $t < $today:
					$text = floor( $t / 3600 ) . '小时前'; // 一天内
					break;
				case $t > $today && $t < $yesterday:
					$text = '昨天' . date( 'H:i' , $time );
					break;
				case $t < 60 * 60 * 24 * 365 && $y == 0:
					$text = date( 'm-d H:i' , $time ); //一年内
					break;
				default:
					$text = date( 'Y-m-d H:i' , $time ); //一年以前
					break;
			}
			return $text;
		}
		
		public static function getWeekDate ( $time = null , $hadDate = 2 , $prefix = '星期' ) : string {
			if ( !$time ) {
				$time = time();
			}
			
			if ( !ctype_digit( $time ) ) {
				$time = strtotime( $time );
			}
			
			$weeks = [ '日' , '一' , '二' , '三' , '四' , '五' , '六' ];
			
			$week = $weeks[date( 'w' , $time )];
			
			if ( 2 == $hadDate ) {
				return $prefix . $week;
			} else {
				return date( 'Y-m-d' , $time ) . ' ' . $prefix . $week;
			}
		}
		
		
		public static function clearUpHtml ( $str , $br = ' ' ) {
			$str = htmlspecialchars_decode( $str ); //把一些预定义的 HTML 实体转换为字符
			$str = str_replace( "&nbsp;" , "" , $str ); //将空格替换成空
			$str = strip_tags( $str ); //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
			if ( $br ) {
				$str = str_replace( [ "\n" , "\r\n" , "\r" ] , $br , $str );
			}
			$preg = "/<script[\s\S]*?<\/script>/i";
			//剥离JS代码
			
			return preg_replace( $preg , "" , $str , -1 );
		}
		
		
		public static function getStrByHtml ( $str , $is_sub = 2 , $l = 100 ) {
			$str  = htmlspecialchars_decode( $str ); //把一些预定义的 HTML 实体转换为字符
			$str  = str_replace( "&nbsp;" , "" , $str ); //将空格替换成空
			$str  = strip_tags( $str ); //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
			$str  = str_replace( [ "\n" , "\r\n" , "\r" ] , ' ' , $str );
			$preg = "/<script[\s\S]*?<\/script>/i";
			$str  = preg_replace( $preg , "" , $str , -1 ); //剥离JS代码
			
			if ( 1 == $is_sub ) {
				if ( mb_strlen( "$str" , 'UTF-8' ) <= $l ) {
					$str = mb_substr( $str , 0 , $l , "utf-8" );
				} else {
					$str = mb_substr( $str , 0 , $l , "utf-8" ) . '...';
				}
			}
			
			//返回字符串中的前100字符串长度的字符
			return $str;
		}
		
		public static function formatHtml ( $str , $image = false ) {
			$preg = "/<script[\s\S]*?<\/script>/i";
			
			$str = preg_replace( $preg , "" , $str );
			
			$str = preg_replace( "/<a[^>]*>(.*)<\/a>/isU" , '${1}' , $str );
			
			$str = str_replace( "\\" , '' , $str );
			
			$str = preg_replace( "/<p([^>]+?)>/" , "<p>" , $str );
			
			$str = preg_replace( "/<div[^>]*>(.*)<\/div>/isU" , '<p>${1}</p>' , $str );
			
			$str = preg_replace( "/<strong[^>]*>(.*)<\/strong>/isU" , '<strong> ${1} </strong>' , $str );
			
			if ( $image ) {
				$str = preg_replace( "/<img[^>]+?(src=['\"].+?['\"])[^>]*?>/" , '' , $str );
			} else {
				$str = preg_replace( "/<img[^>]+?(src=['\"].+?['\"])[^>]*?>/" , "<img $1 >" , $str );
			}
			
			$str = str_replace( "&nbsp;" , '' , $str ); //将空格替换成空
			$str = str_replace( '  ' , '' , $str ); //将空格替换成空
			$str = str_replace( [ "\n" , "\r\n" , "\r" ] , ' ' , $str );
			$str = trim( $str );
			
			//重复替换一次
			return preg_replace( "/<div[^>]*>(.*)<\/div>/isU" , '<p>${1}</p>' , $str );
		}
		
		public static function getMillisecond () : float {
			[ $t1 , $t2 ] = explode( ' ' , microtime() );
			return (float) sprintf( '%.0f' , ( floatval( $t1 ) + floatval( $t2 ) ) * 1000 );
		}
		
		public static function isMobileEnd () : bool {
			// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
			if ( isset( $_SERVER['HTTP_X_WAP_PROFILE'] ) ) {
				return true;
			}
			// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
			if ( isset( $_SERVER['HTTP_VIA'] ) ) {
				// 找不到为flase,否则为true
				return stristr( $_SERVER['HTTP_VIA'] , "wap" ) ? true : false;
			}
			// 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$clientkeywords = [
					'nokia' ,
					'sony' ,
					'ericsson' ,
					'mot' ,
					'samsung' ,
					'htc' ,
					'sgh' ,
					'lg' ,
					'sharp' ,
					'sie-' ,
					'philips' ,
					'panasonic' ,
					'alcatel' ,
					'lenovo' ,
					'iphone' ,
					'ipod' ,
					'blackberry' ,
					'meizu' ,
					'android' ,
					'netfront' ,
					'symbian' ,
					'ucweb' ,
					'windowsce' ,
					'palm' ,
					'operamini' ,
					'operamobi' ,
					'openwave' ,
					'nexusone' ,
					'cldc' ,
					'midp' ,
					'wap' ,
					'mobile' ,
					'MicroMessenger'
				];
				// 从HTTP_USER_AGENT中查找手机浏览器的关键字
				if ( preg_match( "/(" . implode( '|' , $clientkeywords ) . ")/i" , strtolower( $_SERVER['HTTP_USER_AGENT'] ) ) ) {
					return true;
				}
			}
			// 协议法，因为有可能不准确，放到最后判断
			if ( isset ( $_SERVER['HTTP_ACCEPT'] ) ) {
				// 如果只支持wml并且不支持html那一定是移动设备
				// 如果支持wml和html但是wml在html之前则是移动设备
				if ( ( strpos( $_SERVER['HTTP_ACCEPT'] , 'vnd.wap.wml' ) !== false ) && ( strpos( $_SERVER['HTTP_ACCEPT'] , 'text/html' ) === false || ( strpos( $_SERVER['HTTP_ACCEPT'] , 'vnd.wap.wml' ) < strpos( $_SERVER['HTTP_ACCEPT'] , 'text/html' ) ) ) ) {
					return true;
				}
			}
			return false;
		}
		
		
		/**
		 *  判断是否微信浏览器打开
		 */
		public static function isWechatEnd () : bool {
			if ( strpos( $_SERVER['HTTP_USER_AGENT'] , 'MicroMessenger' ) !== false ) {
				
				return true;
				
			} else {
				
				return false;
				
			}
		}
		
		
	}