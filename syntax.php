<?php
/**
 * Minecraft Recipe
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'syntax.php');

//require_once(DOKU_PLUGIN.'mcrecipe/dictionary.php');

class syntax_plugin_mcrecipe extends DokuWiki_Syntax_Plugin {

	function syntax_plugin_mcrecipe() {}

	function getInfo() {
		return array(
			'author' => 'Urs P. Stettler',
			'email'  => 'minecraft@cryx.li',
			'date'   => '2014-07-30',
			'name'   => 'Minecraft Recipe Plugin',
			'desc'   => 'Add Minecraft recipes to dokuwiki.',
			'url'    => ''
		);
	}

	function getType() {
		return 'protected';
	}

	function getSort() {
		return 316;
	}

	function getPType() {
		return 'block';
	}

	function connectTo($mode) {
		$this->Lexer->addEntryPattern('<recipe>', $mode, 'plugin_mcrecipe');
	}

	function postConnect() {
		$this->Lexer->addExitPattern('<\/recipe>', 'plugin_mcrecipe');
	}

	function parseItem($data) {
		if (preg_match('/([^\|,]+),(\d+)/', $data, $group)) {
			return array($group[1], $group[1], intval($group[2]));
        } else if (preg_match('/([^\|,]+)\|([^,]+),(\d+)/', $data, $group)) {
			return array($group[1], $group[2], intval($group[3]));
        } else if (preg_match('/([^\|]+)\|(.+)/', $data, $group)) {
			return array($group[1], $group[2], 1);
		} else {
			return array($data, $data, 1);
		}
	}

	function parseInput($data, $size) {
		$input = array();
		if (preg_match_all('/(input([ \t]+([^\s]+))+)/', $data, $group)) {
			$inputs = $group[0];
			while (count($inputs) < $size['height']) {
				array_push($inputs, 'input ');
			}
			while (count($inputs) > $size['height']) {
				array_pop($inputs);
			}
			foreach($inputs as $line) {
				if (preg_match_all('/([^\s]+)/', substr($line, 6), $group)) {
					$lineItems = array();
					foreach($group[0] as $itemstack) {
						array_push($lineItems, $this->parseItem($itemstack));
					}
					while (count($lineItems) < $size['width']) {
						array_push($lineItems, array('air', 1));
					}
					while (count($lineItems) > $size['width']) {
						array_pop($lineItems);
					}
					$input = array_merge($input, $lineItems);
				} else {
					$input = array_merge($input, array_fill(0, $size['width'], array('air', 1)));
				}
			}
			while (count($input) < $size['width'] * $size['height']) {
				array_push($input, array('air', 1));
			}
			while (count($input) > $size['width'] * $size['height']) {
				array_pop($input);
			}
		} else {
			$input = array_fill(0, $size['width'] * $size['height'], array('air', 1));
		}
		return $input;
	}

	function handle($match, $state, $pos, $handler) {
		$args = array($state);
		switch ($state) {
			case DOKU_LEXER_ENTER:
				$args = array($state);
				break;
			case DOKU_LEXER_UNMATCHED:
				if (preg_match('/size\s+(\dx\d)/', $match, $group)) {
					$size = array('width' => intval(substr($group[1], 0, 1)), 'height' => intval(substr($group[1], 2, 1)));
				} else {
					$size = array('width' => 3, 'height' => 3);
				}
				$input = $this->parseInput($match, $size);
				if (preg_match('/tool\s+(.+)/', $match, $group)) {
					$tool = $this->parseItem($group[1]);
				} else {
					$tool = array('minecraft:crafting_table','minecraft:crafting_table',1);
				}
				if (preg_match('/output\s+(.+)/', $match, $group)) {
					$output = $this->parseItem($group[1]);
				} else {
					$output = array('air', 1);
				}
				$args = array($state, $size, $input, $tool, $output);
				break;
			case DOKU_LEXER_EXIT:
				$args = array($state);
				break;
		}
		return $args;
	}

	function renderItem($renderer, $item) {
		if ($item[0] == 'air') {
			// do nothing
        } else {
			$renderer->doc .= '<a href="doku.php?id=mods:'.$item[0].'">';
			$renderer->doc .= '<img src="lib/exe/fetch.php?media=mods:'.$item[1].'.png" alt="'.$item[0].'" />';
			if ($item[2] > 1) {
				$renderer->doc .= '<span class="number">x'.$item[2].'</span>';
			}
			$renderer->doc .= '</a>';
		}
	}

	function render($format, $renderer, $data) {
		$state = $data[0];
		if (substr($format, 0, 5) == 'xhtml') {
			switch ($state) {
				case DOKU_LEXER_ENTER:
					$renderer->doc .= '<table class="mc-recipe"><tr><td>';
					break;
				case DOKU_LEXER_UNMATCHED:
					$size = $data[1];
					$input = $data[2];
					$tool = $data[3];
					$output = $data[4];

					$index = 0;
					$renderer->doc .= '<table class="mc-input">';
					for ($row = 0; $row < $size['height']; $row++) {
						$renderer->doc .= '<tr>';
						for ($col = 0; $col < $size['width']; $col++) {
							$itemStack = $input[$index++];
							$renderer->doc .= '<td>';
							$this->renderItem($renderer, $itemStack);
							$renderer->doc .= '</td>';
						}
						$renderer->doc .= '</tr>';
					}
					$renderer->doc .= '</table>';

					$renderer->doc .= '</td><td>';

					$renderer->doc .= '<table class="mc-tool"><tr><td>';
					$renderer->doc .= '<img src="'.DOKU_URL.'lib/plugins/mcrecipe/pix/arrow.png" alt="->" />';
					$renderer->doc .= '</td></tr><tr><td>';
					$this->renderItem($renderer, $tool);
					$renderer->doc .= '</td></tr></table>';

					$renderer->doc .= '</td><td>';

					$renderer->doc .= '<table class="mc-output"><tr><td>';
					$this->renderItem($renderer, $output);
					$renderer->doc .= '</td></tr></table>';

					break;
				case DOKU_LEXER_EXIT:
					$renderer->doc .= '</td></tr></table>'.DOKU_LF;
					break;
			}
			return true;
		}
		return false;
	}

}

?>
