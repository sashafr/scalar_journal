function generatePreObject(tagString) {
	var regex = /<pre>|<\/pre>/g;
	tagString = tagString.replace(regex, '');
	var returnObject = {text: tagString, style: "pre"};
	return returnObject;
};

var testPreString = `<pre>Hello World!\n</pre>`;
var testPreObject = generatePreObject(testPreString);
console.log(testPreObject);