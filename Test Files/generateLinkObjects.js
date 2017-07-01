function generateLinkObject (tagString) {
	tagString = tagString.replace('<a href="', '');
	var findQuote = tagString.indexOf('"');
	var linkVal = tagString.substr(0, findQuote);
	tagString = tagString.replace(linkVal+'">', '');
	var linkTextPos = tagString.indexOf('>');
	linkText = tagString.substr(0,linkTextPos);
	linkText = linkText.replace("</a", '');
	var linkObject = {
		text: linkText,
		link: linkVal
	}; 
	return linkObject;
};

var testLinkString = `<a href="http://nintendo.com">Link</a>`;
var testLinkObject = generateLinkObject(testLinkString);
console.log(testLinkObject);