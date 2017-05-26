var correctRegex = /<span[^>]*>/g;

var testString = `<span style="color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align:
justify;">HELLO`;

testString = testString.replace(correctRegex, "");

console.log(testString);