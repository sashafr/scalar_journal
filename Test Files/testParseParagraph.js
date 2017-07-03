var testString = "<em>It is <strong>a truth universally</strong> acknowledged, that a <u><strong>single man in possession of </strong></u>a good fortune, <u>must be in want of a wife</u>.</em> <strong>However little<em> known the feelings or</em> views of such a<u> man may be</u> on<u><em> his first entering a neighbourhood</em></u>,</strong> <u>this <em>truth is so well fixed in the minds of</em><strong> the surrounding families, that he<em> is considered the rightful property</em></strong> of some one or other of their <strong><em>daughters.</em></strong></u>"
function parseParagraph(paragraphString) {
	var inTag = false;
	var tagString = "";
	var tempString = "";
	var returnList = [];
	for (var i = 0; i < paragraphString.length; i++) {
		var charVal = paragraphString.charAt(i);
		if (inTag) {
			if (charVal === ">") {
				if (paragraphString.substring(i-8, i+1) === "</strong>" && tagString === "strong") {
					inTag = false;
					tagString = "";
					returnList.push(tempString);
					tempString = "";
				} else if (paragraphString.substring(i-3, i+1) === "</u>" && tagString === "u") {
					inTag = false;
					tagString = "";
					returnList.push(tempString);
					tempString = "";
				} else if (paragraphString.substring(i-4, i+1) === "</em>" && tagString === "em") {
					inTag = false;
					tagString = "";
					returnList.push(tempString);
					tempString = "";
				} else {
					tempString = tempString + charVal;
				}
			} else {
				tempString = tempString + charVal;
			}
		} else {
			if (charVal === "<") {
				if (paragraphString.substring(i, i+4) === "<em>") {
					inTag = true;
					tagString = "em";
					returnList.push(tempString);
					tempString = "";
				} else if (paragraphString.substring(i, i+3) === "<u>") {
					inTag = true;
					tagString = "u";
					returnList.push(tempString);
					tempString = "";
				} else if (paragraphString.substring(i, i+8) === "<strong>") {
					inTag = true;
					tagString = "strong";
					returnList.push(tempString);
					tempString = "";
				} else {
					tempString = tempString + charVal;
				}
			} else {
				tempString = tempString + charVal;
			}
		}
	}
	returnList.push(tempString);
	return returnList;
}

var testList = parseParagraph(testString);
console.log(testList);