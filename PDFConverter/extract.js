/* Assuming that scalar allows for cross-site resource sharing
 * (which I hope it does, because I can't keep trying to find 
 * where the JSON object gets generated), access the page
 * to get the JSON object, and turn the object string into a JSON object
 */

function extract() {
	// Get text from the jsonDIV element created
	// in the php file
	var jsonString = $("#jsonDIV").text();
	var jsonObj = JSON.parse(jsonString);
	// After conversion to JSON, translate to pdf
	convertToPDFMain(jsonObj);
};