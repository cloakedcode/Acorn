---
layout: default
title: Other Class
---

# {{ page.title }}

Brief description of Other Class.  If it extends a native CodeIgniter class, please link to the class in the CodeIgniter documents here.

<p class="important"><strong>Important:</strong>&nbsp; This is an important note with <kbd>EMPHASIS</kbd>.</p>

Features:

* First
* Second

## Usage Heading

Within a text string, `highlight variables` using <var>&lt;var&gt;&lt;/var&gt;</var> tags, and <dfn>highlight code</dfn> using the <dfn>&lt;dfn&gt;&lt;/dfn&gt;</dfn> tags.

### Sub-heading

Put code examples within <dfn>&lt;code&gt;&lt;/code&gt;</dfn> tags:

{% highlight php %}
<?
$this->load->library('foo');
// Testing foo->bar()
$this->foo->bar('bat');
{% endhighlight %}

## Table Preferences

Use tables where appropriate for long lists of preferences.


<table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
<tr>
	<th>Preference</th>
	<th>Default&nbsp;Value</th>
	<th>Options</th>
	<th>Description</th>
</tr>
<tr>
	<td class="td"><strong>foo</strong></td>
	<td class="td">Foo</td>
	<td class="td">None</td>
	<td class="td">Description of foo.</td>
</tr>
<tr>
	<td class="td"><strong>bar</strong></td>
	<td class="td">Bar</td>
	<td class="td">bat, bag, or bak</td>
	<td class="td">Description of bar.</td>
</tr>
</table>

## Foo Function Reference

### $this->foo->bar()
Description

    $this->foo->bar('<var>baz</var>')
