function fixUpText(titleText) {
	titleText = titleText.replace(/-/g, " ");
	titleText = titleText.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	return titleText;
}

var titleTextToFix = "imax-encode-demo-using-premiere-pro";
var fixedTitle = fixUpText(titleTextToFix);

console.log(fixedTitle);