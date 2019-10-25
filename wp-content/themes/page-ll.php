
<!-- saved from url=(0051)http://jsmachines.sourceforge.net/machines/ll1.html -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>LL(1) Parser Generator</title>
<style>
body * { font-family: courier; }
td { horizontal-align: middle; vertical-align: middle; }
.tree td { vertical-align: top; }
</style>
</head>
<body>
<script language="javascript">
<!--

function $element(id) {
	return document.getElementById(id);
}

var EPSILON = '\'\'';

var alphabet;
var nonterminals;
var terminals;
var rules;
var firsts;
var follows;
var ruleTable;

function grammarChanged() {
	$element('llTableRows').innerHTML = '';
	
	rules = $element('grammar').value.split('\n');
	alphabet = [];
	nonterminals = [];
	terminals = [];
	
	collectAlphabetAndNonterminalsAndTerminals();
	collectFirsts();
	collectFollows();
	makeRuleTable();
	
	displayTable();
	
	parseInput();
}

function displayTable() {
	$element('llTableHead').innerHTML = "<th>FIRST</th><th>FOLLOW</th><th>Nonterminal</th>";
	
	for (var i in terminals) {
		$element('llTableHead').innerHTML += "<th>" + terminals[i] + "</th>";
	}
	
	$element('llTableHead').innerHTML += "<th>$</th>";
	
	for (var i in nonterminals) {
		var nonterminal = nonterminals[i];
		var s = "<tr>";
		s += "<tr>";
		s += "<td nowrap=\"nowrap\">{" + firsts[nonterminal] + "}</td><td nowrap=\"nowrap\">{" + follows[nonterminal] + "}</td><td nowrap=\"nowrap\">" + nonterminal + "</td>";
		
		for (var j in terminals) {
			s += "<td nowrap=\"nowrap\">" + emptyIfUndefined(ruleTable[nonterminal][terminals[j]]) + "</td>";
		}
		
		s += "<td nowrap=\"nowrap\">" + emptyIfUndefined(ruleTable[nonterminal]['$']) + "</td>";
		
		s += "</tr>";
		
		$element('llTableRows').innerHTML  += s;
	}
}

function makeRuleTable() {
	ruleTable = new Object();
	
	for (var i in rules) {
		var rule = rules[i].trim().split('->');
		
		if (rule.length < 2) {
			continue;
		}
		
		var nonterminal = rule[0].trim();
		var development = trimElements(rule[1].trim().split(' '));
		
		var developmentFirsts = collectFirsts3(development);
		
		for (var j in developmentFirsts) {
			var symbol = developmentFirsts[j];
			
			if (symbol != EPSILON) {
				if (ruleTable[nonterminal] == undefined) {
					ruleTable[nonterminal] = new Object();
				}
				
				var oldTableRule = ruleTable[nonterminal][symbol];
				
				if (oldTableRule == undefined) {
					ruleTable[nonterminal][symbol] = rules[i].trim();
				} else {
					ruleTable[nonterminal][symbol] = oldTableRule + "<br>" + rules[i].trim();
				}
			} else {
				for (var j in follows[nonterminal]) {
					var symbol2 = follows[nonterminal][j];
					
					if (ruleTable[nonterminal] == undefined) {
						ruleTable[nonterminal] = new Object();
					}
					
					var oldTableRule = ruleTable[nonterminal][symbol2];
					
					if (oldTableRule == undefined) {
						ruleTable[nonterminal][symbol2] = rules[i].trim();
					} else {
						ruleTable[nonterminal][symbol2] = oldTableRule + "<br>" + rules[i].trim();
					}
				}
			}
		}
	}
}

function emptyIfUndefined(string) {
	return string == undefined ? '' : string;
}

function collectFirsts() {
	firsts = new Object();
	
	var notDone;
	
	do {
		notDone = false;
		
		for (var i in rules) {
			var rule = rules[i].split('->');
			
			if (rule.length < 2) {
				continue;
			}
			
			var nonterminal = rule[0].trim();
			var development = trimElements(rule[1].trim().split(' '));
			var nonterminalFirsts = firsts[nonterminal];
			
			if (nonterminalFirsts == undefined) {
				nonterminalFirsts = [];
			}
			
			if (development.length == 1 && development[0] == EPSILON) {
				notDone |= addUnique(EPSILON, nonterminalFirsts);
			} else {
				notDone |= collectFirsts4(development, nonterminalFirsts);
			}
			
			firsts[nonterminal] = nonterminalFirsts;
		}
	} while (notDone);
}

/**
 * @param development
 * <br>Array of symbols
 * @param nonterminalFirsts
 * <br>Array of symbols
 * <br>Input-output
 * @return <code>true</code> If <code>nonterminalFirsts</code> has been modified
 */
function collectFirsts4(development, nonterminalFirsts) {
	var result = false;
	var epsilonInSymbolFirsts = true;
	
	for (var j in development) {
		var symbol = development[j];
		epsilonInSymbolFirsts = false;

		if (isElement(symbol, terminals)) {
			result |= addUnique(symbol, nonterminalFirsts);
			
			break;
		}
		
		for (var k in firsts[symbol]) {
			var first = firsts[symbol][k];
			
			epsilonInSymbolFirsts |= first == EPSILON;
			
			result |= addUnique(first, nonterminalFirsts);
		}
		
		if (!epsilonInSymbolFirsts) {
			break;
		}
	}
	
	if (epsilonInSymbolFirsts) {
		result |= addUnique(EPSILON, nonterminalFirsts);
	}
	
	return result;
}

/**
 * @param sequence
 * <br>Array of symbols
 */
function collectFirsts3(sequence) {
	var result = [];
	var epsilonInSymbolFirsts = true;
	
	for (var j in sequence) {
		var symbol = sequence[j];
		epsilonInSymbolFirsts = false;
		
		if (isElement(symbol, terminals)) {
			addUnique(symbol, result);
			
			break;
		}
		
		for (var k in firsts[symbol]) {
			var first = firsts[symbol][k];
			
			epsilonInSymbolFirsts |= first == EPSILON;
			
			addUnique(first, result);
		}
		
		epsilonInSymbolFirsts |= firsts[symbol] == undefined || firsts[symbol].length == 0;
		
		if (!epsilonInSymbolFirsts) {
			break;
		}
	}
	
	if (epsilonInSymbolFirsts) {
		addUnique(EPSILON, result);
	}
	
	return result;
}

function collectFollows() {
	follows = new Object();
	
	var notDone;
	
	do {
		notDone = false;
		
		for (var i in rules) {
			var rule = rules[i].split('->');
			
			if (rule.length < 2) {
				continue;
			}
			
			var nonterminal = rule[0].trim();
			var development = trimElements(rule[1].trim().split(' '));
			
			if (i == 0) {
				var nonterminalFollows = follows[nonterminal];
				
				if (nonterminalFollows == undefined) {
					nonterminalFollows = [];
				}
				
				notDone |= addUnique('$', nonterminalFollows);
				
				follows[nonterminal] = nonterminalFollows;
			}
			
			for (var j in development) {
				var symbol = development[j];
				
				if (isElement(symbol, nonterminals)) {
					var symbolFollows = follows[symbol];
					
					if (symbolFollows == undefined) {
						symbolFollows = [];
					}
					
					var afterSymbolFirsts = collectFirsts3(development.slice(parseInt(j) + 1));
					
					for (var k in afterSymbolFirsts) {
						var first = afterSymbolFirsts[k];
						
						if (first == EPSILON) {
							var nonterminalFollows = follows[nonterminal];
							
							for (var l in nonterminalFollows) {
								notDone |= addUnique(nonterminalFollows[l], symbolFollows);
							}
						} else {
							notDone |= addUnique(first, symbolFollows);
						}
					}
			
					follows[symbol] = symbolFollows;
				}
			}
		}
	} while (notDone);
}

function collectAlphabetAndNonterminalsAndTerminals() {
	for (var i in rules) {
		var rule = rules[i].split('->');
		if (rule.length != 2) {
			continue;
		}
		
		var nonterminal = rule[0].trim();
		var development = trimElements(rule[1].trim().split(' '));
		
		addUnique(nonterminal, alphabet);
		addUnique(nonterminal, nonterminals);
		
		for (var j in development) {
			var symbol = development[j];
			
			if (symbol != EPSILON) {
				addUnique(symbol, alphabet);
			}
		}
	}
	
	subtract(alphabet, nonterminals, terminals);
}

/**
 * @param result
 * <br>Array
 * <br>Input-output
 * @return <code>result</code>
 */
function subtract(array1, array2, result) {
	for (var i in array1) {
		var element = array1[i];
		
		if (!isElement(element, array2)) {
			result[result.length] = element;
		}
	}
	
	return result;
}

/**
 * @return
 * <br>Array
 * <br>New
 */
function trimElements(array) {
	var result = [];
	
	for (var i in array) {
		result[i] = array[i].trim();
	}
	
	return result;
}

function isElement(element, array) {
	for (var i in array) {
		if (element == array[i]) {
			return true;
		}
	}
	
	return false;
}

/**
 * @param array
 * <br>Input-output
 * @return <code>true</code> iff <code>array</code> has been modified
 */
function addUnique(element, array) {
	if (!isElement(element, array)) {
		array[array.length] = element;
		
		return true;
	}
	
	return false;
}

function resize(textInput, minimumSize) {
	textInput.size = Math.max(minimumSize, textInput.value.length);
}

function parseInput() {
	var input = $element('input').value.trim().split(' ');
	var stack = ['$', nonterminals[0]];
	var parsingRows = '<tr><td nowrap=\"nowrap\">' + stack.join(' ') + "</td><td nowrap=\"nowrap\">" + input.join(' ') + " $</td nowrap=\"nowrap\"><td></td></tr>\n";
	var maximumStepCount = parseInt($element('maximumStepCount').value);
	var ok = true;
	var tree = new Object();
	tree.label = 'root';
	tree.children = [];
	var parents = [tree];
	
	for (var i = 0, index = 0; i < maximumStepCount && 1 < stack.length; ++i) {
		var stackTop = stack[stack.length - 1];
		var symbol = index < input.length ? input[index] : '$';
		
		if (symbol.trim() == '') {
			symbol = '$';
		}
		
		var rule = '';
		
		if (stackTop == symbol) {
			stack.pop();
			++index;
			parents.pop().children.push(symbol);
		} else {
			if (isElement(stackTop, nonterminals)) {
				rule = ruleTable[stackTop][symbol];
				var node = new Object();
				node.label = stackTop;
				node.children = [];
				parents.pop().children.push(node);
				
				if (rule == undefined) {
					ok = false;
					break;
				}
				
				stack.pop();
				
				var reverseDevelopment = rule.split('->')[1].trim().split(' ').slice(0).reverse();
				
				for (var i in reverseDevelopment) {
					parents.push(node);
				}
				
				if (!isElement(EPSILON, reverseDevelopment)) {
					stack = stack.concat(reverseDevelopment);
				} else {
					parents.pop().children.push(EPSILON);
				}
			} else {
				ok = false;
				break;
			}
		}
		
		parsingRows += '<tr><td nowrap=\"nowrap\">' + stack.join(' ') + "</td><td nowrap=\"nowrap\">" + input.slice(index).join(' ') + " $</td><td nowrap=\"nowrap\">" + rule + "</td></tr>\n";
	}
	
	$element('parsingTableRows').innerHTML = parsingRows;
	
	$element('input').style.color = ok ? 'green' : 'red';
	$element('tree').innerHTML = ok ? toString(tree.children[0]) : '';
}

function toString(tree) {
	if (tree.label == undefined) {
		return '' + tree;
	}
	
	var result = "<table class=\"tree\" border=\"1\"><thead><tr><th colspan=\"" + tree.children.length + "\">" + tree.label + "</th></tr></thead><tbody><tr>";
	
	for (var i in tree.children) {
		result += "<td>" + toString(tree.children[i]) + "</td>";
	}
	
	result += "</tr></tbody></table>";
	
	return result;
}

// -->
</script>
<table><tbody><tr>
<td>
<div>LL(1) grammar ('' is &#949;):</div>
<textarea id="grammar" rows="10" cols="20" onchange="grammarChanged();">E -&gt; T E'
E' -&gt; + T E'
E' -&gt; ''
T -&gt; F T'
T' -&gt; * F T'
T' -&gt; ''
F -&gt; ( E )
F -&gt; id
</textarea>
</td>
<td>
<input type="button" value="&gt;&gt;">
</td>
<td>
<div>
<table border="1"><thead><tr id="llTableHead"><th>FIRST</th><th>FOLLOW</th><th>Nonterminal</th><th>+</th><th>*</th><th>(</th><th>)</th><th>id</th><th>$</th></tr></thead><tbody id="llTableRows"><tr></tr><tr><td nowrap="nowrap">{(,id}</td><td nowrap="nowrap">{$,)}</td><td nowrap="nowrap">E</td><td nowrap="nowrap"></td><td nowrap="nowrap"></td><td nowrap="nowrap">E -&gt; T E'</td><td nowrap="nowrap"></td><td nowrap="nowrap">E -&gt; T E'</td><td nowrap="nowrap"></td></tr><tr></tr><tr><td nowrap="nowrap">{+,''}</td><td nowrap="nowrap">{$,)}</td><td nowrap="nowrap">E'</td><td nowrap="nowrap">E' -&gt; + T E'</td><td nowrap="nowrap"></td><td nowrap="nowrap"></td><td nowrap="nowrap">E' -&gt; ''</td><td nowrap="nowrap"></td><td nowrap="nowrap">E' -&gt; ''</td></tr><tr></tr><tr><td nowrap="nowrap">{(,id}</td><td nowrap="nowrap">{+,$,)}</td><td nowrap="nowrap">T</td><td nowrap="nowrap"></td><td nowrap="nowrap"></td><td nowrap="nowrap">T -&gt; F T'</td><td nowrap="nowrap"></td><td nowrap="nowrap">T -&gt; F T'</td><td nowrap="nowrap"></td></tr><tr></tr><tr><td nowrap="nowrap">{*,''}</td><td nowrap="nowrap">{+,$,)}</td><td nowrap="nowrap">T'</td><td nowrap="nowrap">T' -&gt; ''</td><td nowrap="nowrap">T' -&gt; * F T'</td><td nowrap="nowrap"></td><td nowrap="nowrap">T' -&gt; ''</td><td nowrap="nowrap"></td><td nowrap="nowrap">T' -&gt; ''</td></tr><tr></tr><tr><td nowrap="nowrap">{(,id}</td><td nowrap="nowrap">{*,+,$,)}</td><td nowrap="nowrap">F</td><td nowrap="nowrap"></td><td nowrap="nowrap"></td><td nowrap="nowrap">F -&gt; ( E )</td><td nowrap="nowrap"></td><td nowrap="nowrap">F -&gt; id</td><td nowrap="nowrap"></td></tr></tbody></table>
</div>
</td>
</tr></tbody></table>
<p>Maximum number of steps: <input id="maximumStepCount" type="text" size="3" onkeyup="resize(this, 3);" onchange="parseInput();" value="100"></p>
<p>Input (tokens): <input id="input" type="text" size="10" onkeyup="resize(this, 10);" onchange="parseInput();" value="id + id" style="color: green;"></p>
<p><input type="button" value="GO!"></p>
<br>
<table border="8"><thead><tr><th>Trace</th><th>Tree</th></tr></thead><tbody><tr><td>
<table border="1"><thead><tr><th>Stack</th><th>Input</th><th>Rule</th>
</tr></thead><tbody id="parsingTableRows"><tr><td nowrap="nowrap">$ E</td><td nowrap="nowrap">id + id $</td><td></td></tr>
<tr><td nowrap="nowrap">$ E' T</td><td nowrap="nowrap">id + id $</td><td nowrap="nowrap">E -&gt; T E'</td></tr>
<tr><td nowrap="nowrap">$ E' T' F</td><td nowrap="nowrap">id + id $</td><td nowrap="nowrap">T -&gt; F T'</td></tr>
<tr><td nowrap="nowrap">$ E' T' id</td><td nowrap="nowrap">id + id $</td><td nowrap="nowrap">F -&gt; id</td></tr>
<tr><td nowrap="nowrap">$ E' T'</td><td nowrap="nowrap">+ id $</td><td nowrap="nowrap"></td></tr>
<tr><td nowrap="nowrap">$ E'</td><td nowrap="nowrap">+ id $</td><td nowrap="nowrap">T' -&gt; ''</td></tr>
<tr><td nowrap="nowrap">$ E' T +</td><td nowrap="nowrap">+ id $</td><td nowrap="nowrap">E' -&gt; + T E'</td></tr>
<tr><td nowrap="nowrap">$ E' T</td><td nowrap="nowrap">id $</td><td nowrap="nowrap"></td></tr>
<tr><td nowrap="nowrap">$ E' T' F</td><td nowrap="nowrap">id $</td><td nowrap="nowrap">T -&gt; F T'</td></tr>
<tr><td nowrap="nowrap">$ E' T' id</td><td nowrap="nowrap">id $</td><td nowrap="nowrap">F -&gt; id</td></tr>
<tr><td nowrap="nowrap">$ E' T'</td><td nowrap="nowrap"> $</td><td nowrap="nowrap"></td></tr>
<tr><td nowrap="nowrap">$ E'</td><td nowrap="nowrap"> $</td><td nowrap="nowrap">T' -&gt; ''</td></tr>
<tr><td nowrap="nowrap">$</td><td nowrap="nowrap"> $</td><td nowrap="nowrap">E' -&gt; ''</td></tr>
</tbody></table>
</td><td style="vertical-align: top;">
<div id="tree"><table class="tree" border="1"><thead><tr><th colspan="2">E</th></tr></thead><tbody><tr><td><table class="tree" border="1"><thead><tr><th colspan="2">T</th></tr></thead><tbody><tr><td><table class="tree" border="1"><thead><tr><th colspan="1">F</th></tr></thead><tbody><tr><td>id</td></tr></tbody></table></td><td><table class="tree" border="1"><thead><tr><th colspan="1">T'</th></tr></thead><tbody><tr><td>''</td></tr></tbody></table></td></tr></tbody></table></td><td><table class="tree" border="1"><thead><tr><th colspan="3">E'</th></tr></thead><tbody><tr><td>+</td><td><table class="tree" border="1"><thead><tr><th colspan="2">T</th></tr></thead><tbody><tr><td><table class="tree" border="1"><thead><tr><th colspan="1">F</th></tr></thead><tbody><tr><td>id</td></tr></tbody></table></td><td><table class="tree" border="1"><thead><tr><th colspan="1">T'</th></tr></thead><tbody><tr><td>''</td></tr></tbody></table></td></tr></tbody></table></td><td><table class="tree" border="1"><thead><tr><th colspan="1">E'</th></tr></thead><tbody><tr><td>''</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>
</td></tr></tbody></table>

<script language="javascript">
<!--
grammarChanged();
// -->
</script>

<a href="https://computercau.club/" target="_blank" title="������ҳ">Back to home page</a>
</body>
</html>