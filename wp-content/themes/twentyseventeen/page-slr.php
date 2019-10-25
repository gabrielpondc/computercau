<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!-- saved from url=(0051)http://jsmachines.sourceforge.net/machines/slr.html -->
<!--
/*
 *  The MIT License
 * 
 *  Copyright 2011 Greg.
 * 
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 * 
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 * 
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */
--><HTML><HEAD><META 
content="IE=5.0000" http-equiv="X-UA-Compatible">
 <TITLE>SLR文法编译工具</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<STYLE>
body { background-color: #F0F0F0; }
body * { font-family:courier; }
td { horizontal-align: middle; vertical-align: top; white-space: nowrap; }
th { white-space: nowrap; }
</STYLE>
 
<SCRIPT language="javascript" src="slr_files/underscore.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/tools.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/grammar.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/grammarview.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/slritem.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/lrclosuretable.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/lrclosuretableview.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/tree.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/treeview.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/lrtable.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/lrtableview.js"></SCRIPT>
 
<SCRIPT language="javascript" src="slr_files/lrparseview.js"></SCRIPT>
 
<META name="GENERATOR" content="MSHTML 11.00.10570.1001"></HEAD> 
<BODY>
<SCRIPT language="javascript">
<!--

var lrTable;

function grammarChanged() {
	displayRuleIndices();
	
	var grammar = new Grammar($element('grammar').value);
	var lrClosureTable = new LRClosureTable(grammar);
	lrTable = new LRTable(lrClosureTable);

	$element('firstFollowView').innerHTML = formatFirstFollow(grammar);
	$element('lrClosureTableView').innerHTML = formatLRClosureTable(lrClosureTable);
	$element('lrTableView').innerHTML = formatLRTable(lrTable);
	
	parseInput();
}

// -->
</SCRIPT>
 
<TABLE>
  <TBODY>
  <TR>
    <TD>
      <DIV id="grammarView"></DIV><BR>
      <DIV id="firstFollowView"></DIV></TD>
    <TD><BR><INPUT type="button" value=">>"> </TD>
    <TD>
      <DIV style="height: 100%; overflow: auto;">
      <DIV id="lrClosureTableView"></DIV><BR>
      <HR>
      <BR>
      <TABLE border="1" cellpadding="8">
        <TBODY>
        <TR>
          <TD>
            <DIV id="lrTableView"></DIV></TD>
          <TD>
            <DIV 
id="lrParseView"></DIV></TD></TR></TBODY></TABLE></DIV></TD></TR></TBODY></TABLE>
<SCRIPT language="javascript">
<!--

// <TEST>

{
	/*
	 * (0) A' -> A
	 * (1) A -> a A
	 * (2) A -> a
	 *
	 * FIRST(A') = { a }
	 * FIRST(A) = { a }
	 *
	 * FOLLOW(A') = { $ }
	 * FOLLOW(A) = { $ }
	 */
	var grammar = new Grammar('A\' -> A\nA -> a A\nA -> a');
	
	assertEquality('A\'', grammar.axiom);
	assertEquality(3, grammar.rules.length);
	assertEquality(['a'].toString(), grammar.firsts['A'].toString());
	assertEquality(['$'].toString(), grammar.follows['A'].toString());
	
	assertEquals(new Item(new Rule(grammar, 'A -> a A'), 1), new Item(new Rule(grammar, 'A -> a A'), 1));
	assertEquality(0, indexOfUsingEquals(new Item(new Rule(grammar, 'A -> a A'), 1), [new Item(new Rule(grammar, 'A -> a A'), 1)]));
	
	/*
	 *              closure { A' -> .A }          = 0 = { A' -> .A; A -> .a A; A -> .a }
	 * goto(0, A) = closure { A' -> A. }          = 1 = { A' -> A. }
	 * goto(0, a) = closure { A -> a.A; A -> a. } = 2 = { A -> a.A; A -> a.; A -> .a A; A -> .a }
	 * goto(2, A) = closure { A -> a A. }         = 3 = { A -> a A. }
	 * goto(2, a) = closure { A -> a.A; A -> a. } = 2
	 */
	var lrClosureTable = new LRClosureTable(grammar);
	
	assertEquality(3, lrClosureTable.kernels[0].closure.length);
	assertEquality(4, lrClosureTable.kernels.length);
	
	/*
	 *   a  $  A' A
	 * 0 s2       1
	 * 1    r0
	 * 2 s2 r2    3
	 * 3    r1
	 */
	var lrTable = new LRTable(lrClosureTable);
	
	assertEquality(4, lrTable.states.length);
	assertEquality('s2', lrTable.states[0]['a']);
	assertEquality('r0', lrTable.states[1]['$']);
	assertEquality('s2', lrTable.states[2]['a']);
	assertEquality('r2', lrTable.states[2]['$']);
	assertEquality('3', lrTable.states[2]['A']);
	assertEquality('r1', lrTable.states[3]['$']);
}

{
	/*
	 * (0) A' -> A
	 * (1) A -> B
	 * (2) A -> ''
	 * (3) B -> ( A )
	 *
	 * FIRST(A') = { '', ( }
	 * FIRST(A) = { '', ( }
	 * FIRST(B) = { ( }
	 *
	 * FOLLOW(A') = { $ }
	 * FOLLOW(A) = { $, ) }
	 * FOLLOW(B) = { $, ) }
	 */
	var grammar = new Grammar('A\' -> A\nA -> B\nA -> \'\'\nB -> ( A )');
	
	assertEquality('A\'', grammar.axiom);
	assertEquality(4, grammar.rules.length);
	assertEquality([EPSILON, '('].toString(), grammar.firsts['A'].toString());
	assertEquality(['$', ')'].toString(), grammar.follows['A'].toString());
	assertEquality('A -> .b', new Item(new Rule(grammar, 'A -> b'), 0).toString());
	
	/*
	 *              closure { A' -> .A }    = 0 = { A' -> .A; A -> .B; A -> .; B -> .( A ) }
	 * goto(0, A) = closure { A' -> A. }    = 1 = { A' -> A. }
	 * goto(0, B) = closure { A -> B. }     = 2 = { A -> B. }
	 * goto(0, () = closure { B -> (.A ) }  = 3 = { B -> (.A ); A -> .B; A -> .; B -> .( A ) }
	 * goto(3, A) = closure { B -> ( A.) }  = 4 = { B -> ( A.) }
	 * goto(3, B) = closure { A -> B. }     = 2
	 * goto(3, () = closure { B -> (.A ) }  = 3
	 * goto(4, )) = closure { B -> ( A ). } = 5 = { B -> ( A ). }
	 */
	var lrClosureTable = new LRClosureTable(grammar);
	
	assertEquality(4, lrClosureTable.kernels[0].closure.length);
	assertEquality(6, lrClosureTable.kernels.length);
	
	/*
	 *   (  )  $  A' A B
	 * 0 s3 r2 r2    1 2
	 * 1       r0
	 * 2    r1 r1
	 * 3 s3 r2 r2    4 2
	 * 4    s5
	 * 5    r3 r3
	 */
	var lrTable = new LRTable(lrClosureTable);
	
	assertEquality(6, lrTable.states.length);
	assertEquality('s3', lrTable.states[0]['(']);
	assertEquality('r0', lrTable.states[1]['$']);
	assertEquality('4', lrTable.states[3]['A']);
}

// </TEST>

// <INITIALIZATION>

{
	var grammar = new Grammar('E\' -> E\n\
E -> E + T\n\
E -> T\n\
T -> T * F\n\
T -> F\n\
F -> ( E )\n\
F -> id');

	$element('grammarView').innerHTML = formatGrammar(grammar);
}

$element('lrParseView').innerHTML = formatInitialParseView('id + id * id', 100);

grammarChanged();

// </INITIALIZATION>

// -->
</SCRIPT>
<h4><a href="https://www.computercau.club/" target="_blank" title="首页">回到首页</a></h4>
<h3>使用方法</h3>
<h4>1.''代表ε</h4>
<h4>2.不同教授要求不同，构建LR表时候acc被当作r1，那么原本的r1变为r2以此类推</h4>
<h4>3.若遇到如S ->a|b的情况需要列为S -> a和S -> b</h4>
<h4>4.所有文法必须按照 例：S空格->空格A空格B空格''的方式所有符号必须为英文符</h4>
 </BODY></HTML>
