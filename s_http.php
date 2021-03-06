<?php

	/************************************************
	 * s_http - samborsky_http
	 * 
	 * Упрощенный класс для работы с http протоколом на базе 
	 * библиотеки curl
	 *  
	 * Версия: 1.02
	 * Начало работ: 06.05.2008
	 * 
	 *************************************************/
    
	class s_http{
		
		// Хендл
		private $curl = NULL;
		
		// Последний урл
		var $url;
		
		// Последняя POST DATA
		var $post_data;
		
		// Скачанные данные
		var $data;
		
		// User Agent
		var $user_agent = 'Mozilla/11.0 (Windows; U; Windows NT 5.2; ru; rv:1.9.0.8) Gecko/2011032609 Firefox/11.0.8';
		
		// String: Последняя ошибка
		var $error;
		var $referer;
		
        var $proxy;
        
/*
        
61.246.139.63:80
77.249.93.97:80
75.125.163.210:3128
187.108.227.171:3128
87.76.32.31:3128
31.25.137.251:8080
190.144.13.66:3128
213.138.72.242:3128

*/
        
		/***
		 * Инициализация
		 */
		function init(){
			
			$this->curl = curl_init();
			
			if( !$this->curl ){
				$this->error = curl_error($this->curl);
				return;
			}
			
			$this->set_opt(CURLOPT_RETURNTRANSFER,true);
			$this->set_opt(CURLOPT_CONNECTTIMEOUT,15);
			$this->set_opt(CURLOPT_USERAGENT,$this->user_agent);
			$this->set_opt(CURLOPT_HEADER,false);
			$this->set_opt(CURLOPT_ENCODING,'gzip,deflate');
			$this->set_opt(CURLOPT_FOLLOWLOCATION,true);

			// Кукисы
			$this->set_opt(CURLOPT_COOKIESESSION,true);
			$this->set_opt(CURLOPT_COOKIEFILE,'cookiefile');
			
			// Если Referer не задан, включаем автореферер. Как в браузерах
			if( !empty($this->referer) ){
				$this->set_opt(CURLOPT_REFERER,$this->referer);
			}
            
            if(!empty($this->proxy)){
                $this->set_opt(CURLOPT_PROXY, $this->proxy);
                echo "Load proxy:".$this->proxy."\n";
            }
		}
		
		/***
		 * Деструктор
		 */
	    function __destruct() {
	    	if( $this->curl ){
				curl_close($this->curl);
				$this->curl = NULL;
			}
	    }

	    /***
	     * Последняя ошибка
	     */
	    function error(){
	    	return $this->error;
	    }
	    
	    /***
	     * Скачанные данные в виде строки
	     */
	    function data(){
	    	return $this->data;
	    }
		
	    /***
	     * Внутренняя функция. Устанавливаем опцию
	     */
		private function set_opt($opt,$val){
			if( !curl_setopt($this->curl,$opt,$val) ){
				
				$this->error = curl_error($this->curl);
				return false;
			}
			return true;
		}
		
		/***
		 * Сохраняем скачанные данные в файл
		 */
		function to_file($name){
			
			if( $f = fopen($name,'w') ){
				
				fwrite($f,$this->data);
				fclose($f);
				return true;
			}
			else{
				$this->error = 'Не удалось записать в файл. Проверьте правильность пути или права на файл.';
			}
			
			return false;
		}
		
		/***
		 * Обычный GET запрос
		 */
		function get($url){
			
			$this->url = $url;
			
			if( empty($this->url) ){
				$this->error = 'Не указан URL';
				return false;
			}
			
			$this->set_opt(CURLOPT_URL,$this->url);
			$this->set_opt(CURLOPT_POST,false);

			return $this->exec();			
		}
		
		/***
		 * https GET запрос
		 */
		function https_get($url){
			
			$this->url = $url;
			
			if( empty($this->url) ){
				$this->error = 'Не указан URL';
				return false;
			}
			
			$this->set_opt(CURLOPT_URL,$this->url);
			$this->set_opt(CURLOPT_SSL_VERIFYHOST,0);
			$this->set_opt(CURLOPT_SSL_VERIFYPEER,false);
			
			return $this->exec();			
		}
		
		/***
		 * Внутренняя функция. Выполняет запрос
		 */
		private function exec(){
			
			if( false == ($this->data = curl_exec($this->curl)) ){
				
				$this->error = curl_error($this->curl);
				return false;
			} 
			
			return true;
		}
		
		/***
		 * Обычный POST запрос
		 */
		function post($url,$post_data){
			$this->url = $url;
			$this->post_data = $post_data;
			
			if( empty($this->url) ){
				$this->error = 'Не указан URL либо POST DATA';
				return false;
			}
			
			$this->set_opt(CURLOPT_URL,$this->url);

			// POST
			$this->set_opt(CURLOPT_POST,true);
			$this->set_opt(CURLOPT_POSTFIELDS,$this->post_data);
			//$this->set_opt(CURLOPT_REFERER, "http://www.avito.ru/profile");
            if( !empty($this->referer) ){
                $this->set_opt(CURLOPT_REFERER,$this->referer);
            }
			return $this->exec();
		}
	}	
	
?>