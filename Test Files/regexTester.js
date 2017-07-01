var correctRegex = /<span[^>]*>/g;

var testString = `<span style="color: rgb(0, 0, 0); font-family: 'Open Sans', Arial, sans-serif; font-size: 14px; text-align:
justify;">HELLO`;

var testString2 = `an style="background-color:#008000;"Ad quod minim posidonium duospan>, legere eruditi habemus an vim, mel quod tibique ei.`;

var testString3 = `,</span> <span style="color:#2F4F4F;"><span style="background-color:#800000;">mel quod tibique ei.</span></span>"`

testString = testString.replace(correctRegex, "");
var testString2Match = testString2.match(/an style=.*>/);
var testString3Match = testString3.match(/<span style=.*;"><span style=.*;">.*<\/span><\/span>/);
var testString3MatchFake = testString3.match(/chungus/);

console.log(testString);
console.log(testString2Match);
console.log(testString3Match);
console.log(testString3MatchFake);