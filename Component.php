<?php
	class Component {
		private $dir = 'modules/';
		private $path;
		protected $props = [];
		private $tags = [
			'\{ foreach ([A-Za-z0-9_]+) as ([A-Za-z0-9_]+) \}' => '<?php foreach($this -> props["$1"] as $$2): ?>',
			'\{ endforeach \}' => '<?php endforeach; ?>',
			'\{ if ([^}]*) \}' => '<?php if($this -> props["$1"]): ?>',
			'\{ elseif ([^}]*) \}' => '<?php elseif($this -> props["$1"]): ?>',
			'\{ else \}' => '<?php else: ?>',
			'\{\ endif \}' => '<?php endif; ?>',
			'\{ component ([A-Za-z0-9_]+) \}' => '<?php $_cp = new Component($this -> props["$1"]); $_cp -> render(); ?>',
			'\{ component ([^}]*) \}' => '<?php $_cp = new Component("$1"); $_cp -> render(); ?>',
			'\{ function ([A-Za-z0-9_]+) \}' => '<?php echo $model -> $1(); ?>',
			'\{ ([A-Za-z0-9_]+)\.([^}]*) \}' => '<?php echo $this -> props["$1"]["$2"]; ?>',
			'\{ ([A-Za-z0-9_]+) \}' => '<?php echo $this -> props["$1"]; ?>',
			'\{ \.([A-Za-z0-9_]+) \}' => '<?php echo $$1; ?>',
		];
		
		public function __construct($path = '') {
			$this -> path = $path;
		}

		public function render() {
			$dirName = '/'.substr($this -> path, strrpos($this -> path, '/') + 1);
			$modelName = str_replace('/', '\\', ucwords($this -> path.$dirName, '/'));

			if(class_exists($modelName)) {
				$model = new $modelName();
				$this -> props = array_merge($this -> props, $model -> props);
			}

			$tpl = file_get_contents($this -> dir.$this -> path.$dirName.'.tpl');
			$content = $this -> replace_tags($tpl);
			eval("?> $content");
		}
		
		public function set($key, $value) {
			$this -> props[$key] = $value;
		}

		public function get($key) {
			return $this -> props[$key];
		}

		private function replace_tags($content) {
			foreach($this -> tags as $tag => $php) {
				$content = preg_replace('/'.$tag.'/', $php, $content);
			}
			
			return $content;
		}
	}