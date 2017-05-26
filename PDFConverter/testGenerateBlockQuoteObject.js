function generateBlockQuoteObject(tagString) {
	var blockQuoteRegex = /<blockquote>|<p>|<\/blockquote>|<\/p>/g;
	tagString = tagString.replace(blockQuoteRegex, '');
	var blockObject = {
		text: tagString,
		style: 'quoteFormat'
	}; 
	return blockObject;
};

var blockQuoteString = "<blockquote><p>This is a quote</p></blockquote>";
var blockQuoteObject = generateBlockQuoteObject(blockQuoteString);
console.log(blockQuoteObject);