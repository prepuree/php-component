<?php
	class Component {
		private $dir = '';
		private $pipesDir = 'pipes/';
		private $path;
		private $dirName;
		protected $props = [];
		protected $css = [];
		protected $js = [];
		private $tags = [
			'\{ foreach \$([A-Za-z0-9_]+) as ([A-Za-z0-9_]+) \}' => '<?php foreach($this -> props["$1"] as $$2): ?>',
			'\{ foreach \$([A-Za-z0-9_]+).([A-Za-z0-9_]+) as ([A-Za-z0-9_]+) \}' => '<?php foreach($this -> props["$1"]["$2"] as $$3): ?>',
			'\{ endforeach \}' => '<?php endforeach; ?>',

			'\{ component ([A-Za-z0-9_]+) \}' => '<?php $_cp = new Component($this -> props["$1"]); $_cp -> render(); $this -> merge($_cp); ?>',
			'\{ component ([^}]*) \}' => '<?php $_cp = new Component("$1"); $_cp -> render(); $this -> merge($_cp); ?>',
			'\{ path ([^}]*) \}' => '<?php echo $this -> dir.$this -> path."/"."$1"; ?>',
			'\{ function ([A-Za-z0-9_]+) \}' => '<?php echo $model -> $1(); ?>',

			'\{ \$([A-Za-z0-9_]+)\.([^}]*) \| ([^}]*) \}' => '<?php echo $this -> loadPipe($this -> props["$1"]["$2"], "$3"); ?>',
			'\{ \$([A-Za-z0-9_]+) \| ([^}]*) \}' => '<?php echo $this -> loadPipe($this -> props["$1"], "$2"); ?>',
			'\{ \.([A-Za-z0-9_]+) \| ([^}]*) \}' => '<?php echo $this -> loadPipe($$1, "$2"); ?>',
			
			'\{ \$([A-Za-z0-9_]+)\.([^}]*) \}' => '<?php echo $this -> props["$1"]["$2"]; ?>',
			'\{ \$([A-Za-z0-9_]+) \}' => '<?php echo $this -> props["$1"]; ?>',
			'\{ \.([A-Za-z0-9_]+) \}' => '<?php echo $$1; ?>',

			'\$([A-Za-z0-9_]+)\.([^}]*)' => '$this -> props["$1"]["$2"]',
			'\$([A-Za-z0-9_]+)' => '$this -> props["$1"]',
			'\.([A-Za-z0-9_]+)' => 'echo $$1',
		];

		private $specialTags = [
			'\{ if ([^}]*) \}' => '<?php if($1): ?>',
			'\{ elseif ([^}]*) \}' => '<?php elseif($1): ?>',
			'\{ else \}' => '<?php else: ?>',
			'\{ endif \}' => '<?php endif; ?>',
			
			'\{% css %\}' => '<?php $this -> css(); ?>',
			'\{% js %\}' => '<?php $this -> js(); ?>'
		];
		
		public function __construct($path = '') {
			$this -> path = $path;
			$this -> dirName = '/'.substr($path, strrpos($path, '/') + 1);

			$cssFile = $this -> dir.$this -> path.$this -> dirName.'.css';
			if(file_exists($cssFile)) array_push($this -> css, $cssFile);

			$jsFile = $this -> dir.$this -> path.$this -> dirName.'.js';
			if(file_exists($jsFile)) array_push($this -> js, $jsFile);
		}

		public function render() {
			$modelName = str_replace('/', '\\', ucwords($this -> path.$this -> dirName, '/'));

			if(class_exists($modelName)) {
				$model = new $modelName();
				$this -> props = array_merge($this -> props, $model -> props);
			} 

			$content = $this -> prerender();
			$content = $this -> replace_tags($content, $this -> specialTags);
			ob_start();
				eval("?> $content");
				$content = ob_get_contents();
			ob_end_clean();
			echo $content;
		}

		private function prerender() {
			$tpl = file_get_contents($this -> dir.$this -> path.$this -> dirName.'.tpl');
			$content = $this -> replace_tags($tpl, $this -> tags);
			ob_start();
				eval("?> $content");
				$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		
		public function set($key, $value) {
			$this -> props[$key] = $value;
		}

		public function get($key) {
			return $this -> props[$key];
		}

		private function replace_tags($content, $tags) {
			foreach($tags as $tag => $php) {
				$content = preg_replace('/'.$tag.'/', $php, $content);
			}
			
			return $content;
		}

		private function merge($class) {
			$this -> css = array_merge($this -> css, $class -> css);
			$this -> js = array_merge($this -> js, $class -> js);
		}

		protected function css() {
			foreach($this -> css as $file) {
				echo '<link rel="stylesheet" href="./'.$file.'">';
			}
		}

		protected function js() {
			foreach($this -> js as $file) {
				echo '<script src="./'.$file.'"></script>';
			}
		}

		private function loadPipe($prop, $pipeStr) {
			$result = '';
			$pipes = explode(' | ', $pipeStr);
			foreach($pipes as $pipe) {
				$pipeArgs = explode(':', $pipe);
				$pipeName = array_shift($pipeArgs);
				array_unshift($pipeArgs, ( $result ? $result : $prop));
				$pipePath = $this -> pipesDir.$pipeName.'.php';
				if(file_exists($pipePath)) {
					require_once $pipePath;
					if(is_callable($pipeName)) $result = call_user_func_array($pipeName, $pipeArgs);
				}
			}

			return $result;
		}
	}