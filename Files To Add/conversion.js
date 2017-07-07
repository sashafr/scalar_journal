var globalURL;

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}
/* Assuming that CORS is enabled, get the image from
   the URL, return the object to hold the image data */
function getBase64FromImageUrl(url, callback){
    console.log(url);
    var canvas = document.createElement('CANVAS'),
    ctx = canvas.getContext('2d'),
    img = new Image;
    img.crossOrigin = 'Anonymous';
    await sleep(2000);
    img.onload = function(){
        var dataURL;
        canvas.height = img.height;
        canvas.width = img.width;
        ctx.drawImage(img, 0, 0);
        dataURL = canvas.toDataURL('image/png');
        callback(dataURL);
        canvas = null; 
    };
    img.src = url;
}

/* We're making the titles of link the resource value of the link tags.
   This needs to be cleaned up so that there are no - characters, and that
   things are properly capitalized */
function fixUpText(titleText) {
    titleText = titleText.replace(/-/g, " ");
    titleText = titleText.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    return titleText;
}

/* Go through the paragraph to split via italics, bold, and underline tags */
/* Later on, let's see if I can make this more regex-based for sake of simplicity */
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

/* From a 3D matrix of text values, convert the matrix value to a suitable tag
   There has to be a better way to deal with this. */
function generateObjectsFromParagraphMatrix(matrix) {
    var firstTag;
    var secondTag;
    var thirdTag;
    var pushObject = {};
    var pushList = [];
    for (var i = 0; i < matrix.length; i++) {
        firstTag = null;
        for (var j = 0; j < matrix[i].length; j++) {
            secondTag = null;
            for (var k = 0; k < matrix[i][j].length; k++) {
                thirdTag = null;
                if (matrix[i][j][k] === "") {
                    continue;
                }
                if (matrix[i][j][k].includes("em>") && !(matrix[i][j][k].includes("</em"))) {
                    if (firstTag === null) {
                        firstTag = "em";
                    } else if (secondTag === null) {
                        secondTag = "em";
                    } else {

                    }
                    var pushString = matrix[i][j][k].replace(/em>/, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === "em" && secondTag === null) {
                        pushObject = {text: pushString, italics: true};
                    } else if (firstTag === "strong" && secondTag === "em") {
                        pushObject = {text: pushString, bold: true, italics: true};
                    } else if (firstTag === "u" && secondTag === "em") {
                        pushObject = {text: pushString, italics: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                } else if (matrix[i][j][k].includes("u>") && !(matrix[i][j][k].includes("</u"))) {
                    if (firstTag === null) {
                        firstTag = "u";
                    } else if (secondTag === null) {
                        secondTag = "u"
                    } else {

                    }
                    var pushString = matrix[i][j][k].replace(/u>/, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === "u" && secondTag === null) {
                        pushObject = {text: pushString, decoration: "underline"};
                    } else if (firstTag === "strong" && secondTag === "u") {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else if (firstTag === "em" && secondTag === "u") {
                        pushObject = {text: pushString, italics: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                } else if (matrix[i][j][k].includes("strong>") && !(matrix[i][j][k].includes("</strong"))) {
                    if (firstTag === null) {
                        firstTag = "strong";
                    } else if (secondTag === null) {
                        secondTag = "strong";
                    } else {

                    }
                    var pushString = matrix[i][j][k].replace(/strong>/, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === "strong" && secondTag === null) {
                        pushObject = {text: pushString, bold: true};
                    } else if (firstTag === "em" && secondTag === "strong") {
                        pushObject = {text: pushString, bold: true, italics: true};
                    } else if (firstTag === "u" && secondTag === "strong") {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                } else if (matrix[i][j][k].includes("em>") && matrix[i][j][k].includes("</em")) {
                    var pushString = matrix[i][j][k].replace(/em>|<\/em/g, "");
                    if (pushString === "") {
                        continue;
                    }
                    if (firstTag === null && secondTag === null) {
                        pushObject = {text: pushString, italics: true};
                    } else if (firstTag === "strong" && secondTag === "u") {
                        pushObject = {text: pushString, bold: true, decoration: "underline", italics: true};
                    } else if (firstTag === "u" && secondTag === "strong") {
                        pushObject = {text: pushString, bold: true, decoration: "underline", italics: true};
                    } else if (firstTag === "strong" && secondTag === null) {
                        pushObject = {text: pushString, bold: true, italics: true};
                    } else if (firstTag === "u" && secondTag === null) {
                        pushObject = {text: pushString, italics: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                } else if (matrix[i][j][k].includes("u>") && matrix[i][j][k].includes("</u")) {
                    var pushString = matrix[i][j][k].replace(/u>|<\/u/g, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === null && secondTag === null) {
                        pushObject = {text: pushString, decoration: "underline"};
                    } else if (firstTag === "strong" && secondTag === "em") {
                        pushObject = {text: pushString, italics: true, decoration: "underline", bold: true};
                    } else if (firstTag === "em" && secondTag === "strong") {
                        pushObject = {text: pushString, italics: true, decoration: "underline", bold: true};
                    } else if (firstTag === "strong" && secondTag === null) {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else if (firstTag === "em" && secondTag === null) {
                        pushObject = {text: pushString, italics: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                } else if (matrix[i][j][k].includes("strong>") && matrix[i][j][k].includes("</strong")) {
                    var pushString = matrix[i][j][k].replace(/strong>|<\/strong/g, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === null && secondTag === null) {
                        pushObject = {text: pushString, bold: true};
                    } else if (firstTag === "em" && secondTag === "u") {
                        pushObject = {text: pushString, italics: true, decoration: "underline", bold: true};
                    } else if (firstTag === "u" && secondTag === "em") {
                        pushObject = {text: pushString, italics: true, decoration: "underline", bold: true};
                    } else if (firstTag === "em" && secondTag === null) {
                        pushObject = {text: pushString, italics: true, bold: true};
                    } else if (firstTag === "u" && secondTag === null) {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                } else if (!(matrix[i][j][k].includes("em>")) && matrix[i][j][k].includes("</em")) {
                    var pushString = matrix[i][j][k].replace(/<\/em/, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === "u" && secondTag === null) {
                        pushObject = {text: pushString, decoration: "underline"};
                    } else if (firstTag === "strong" && secondTag === "u") {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else if (firstTag = "em" && secondTag == "u") {
                        pushObject = {text: pushString, italics: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                    if (firstTag === "em") {
                        firstTag = null;
                        secondTag = null;
                    } else if (secondTag === "em") {
                        secondTag = null;
                    } else {

                    }
                } else if (!(matrix[i][j][k].includes("u>")) && matrix[i][j][k].includes("</u")) {
                    var pushString = matrix[i][j][k].replace(/<\/u/, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === "u" && secondTag === null) {
                        pushObject = {text: pushString, decoration: "underline"};
                    } else if (firstTag === "strong" && secondTag === "u") {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else if (firstTag === "em" && secondTag === "u") {
                        pushObject = {text: pushString, italics: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                    if (firstTag === "u") {
                        firstTag = null;
                        secondTag = null;
                    } else if (secondTag === "u") {
                        secondTag = null;
                    } else {

                    }
                } else if (!(matrix[i][j][k].includes("strong>")) && matrix[i][j][k].includes("</strong")) {
                    var pushString = matrix[i][j][k].replace(/<\/strong/, "");
                    if (pushString === "") {
                        continue;
                    }

                    if (firstTag === "strong" && secondTag === null) {
                        pushObject = {text: pushString, bold: true};
                    } else if (firstTag === "em" && secondTag === "strong") {
                        pushObject = {text: pushString, bold: true, italics: true};
                    } else if (firstTag === "u" && secondTag === "strong") {
                        pushObject = {text: pushString, bold: true, decoration: "underline"};
                    } else {

                    }
                    pushList.push(pushObject);
                    if (firstTag === "strong") {
                        firstTag = null;
                        secondTag = null;
                    } else if (secondTag === "strong") {
                        secondTag = null;
                    } else {

                    }
                } else {
                    var pushString = matrix[i][j][k];
                    if (pushString === "") {
                        continue;
                    }
                    if (firstTag === "strong" && secondTag === null) {
                        pushObject = {text: pushString, bold: true};
                    } else if (firstTag === "em" && secondTag === null) {
                        pushObject = {text: pushString, italics: true};
                    } else if (firstTag === "u" && secondTag === null) {
                        pushObject = {text: pushString, decoration: "underline"};
                    } else {
                        pushObject = {text: pushString};     
                    }
                    pushList.push(pushObject);
                }
            }
        }
    }
    return pushList;
}

/* Filter out the description-based tags so that they aren't in the description when 
   the pdf gets created */
function filterOut(descriptionString) {
    if (descriptionString.includes("DescTags:")) {
        descriptionString = descriptionString.replace(/DescTags: .*/, "");
    } else if (descriptionString.includes("Desc Tags:")) {
        descriptionString = descriptionString.replace(/Desc Tags: .*/, "");
    } else if (descriptionString.includes("Description Tags:")) {
        descriptionString = descriptionString.replace(/Description Tags: .*/, "");
    } else if (descriptionString.includes("Tags:")) {
        descriptionString = descriptionString.replace(/Tags: .*/, "");
    } else {

    }
    return descriptionString;
}

/* Generate appropriate link element for the CTDA */
function generateCTDAElement(paragraphString) {
    var hrefMatch = paragraphString.match(/href=".*" r|href=".*">/)[0];
    var textMatch = paragraphString.match(/resource=".*" data-size/)[0];
    hrefMatch = hrefMatch.replace(/href="|" r/g, "");
    hrefMatch = hrefMatch.replace(/">/, "");
    textMatch = textMatch.replace(/resource="|" data-size/g, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: hrefMatch, style: "linkBody"};
    return returnObject;
}

/* Generate appropriate link element for NYU */
function generateNYUElement(paragraphString) {
    //console.log(paragraphString);
    var hrefMatch = paragraphString.match(/href=".*" r|href=".*">/)[0];
    var textMatch = paragraphString.match(/resource=".*" data-size/)[0];
    hrefMatch = hrefMatch.replace(/href="|" r/g, "");
    hrefMatch = hrefMatch.replace(/">/, "");
    textMatch = textMatch.replace(/resource="|" data-size/g, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: hrefMatch, style: "linkBody"};
    return returnObject;
}

/* Generate appropriate link element for youtube links (that you generate from Scalar)
   Also works for the USC Holocaust thing that Scalar also has */
function generateYoutubeLinkElement(paragraphString) {
    var hrefMatch = paragraphString.match(/href=".*"/)[0];
    var textMatch = paragraphString.match(/resource=".*" data-size/)[0];
    textMatch = textMatch.replace(/resource="/, "");
    textMatch = textMatch.replace(/".*/, "");
    hrefMatch = hrefMatch.replace(/href="|"/g, "");
    hrefMatch = hrefMatch.replace(/v\//, "watch?v=");
    hrefMatch = hrefMatch.replace(/resource=.*/, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: hrefMatch, style: "linkBody"};
    return returnObject;  
}

/* Generate link for Critical Commons elements */
function generateCriticalCommonsVideoLinkElement(paragraphString) {
    var linkMatch = paragraphString.match(/http:\/\/.*"|http:\/\/.*" r/)[0];
    linkMatch = linkMatch.replace(/" r|"/, "");
    var textMatch = paragraphString.match(/resource=".*" data-size/)[0];
    textMatch = textMatch.replace(/resource="|" data-size/g, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: linkMatch, style: "linkBody"};
    return returnObject;
}

/* Generate link for Internet Archive elements */
function generateInternetArchiveLinkElement(paragraphString) {
    var linkMatch = paragraphString.match(/http:\/\/.*" r|http:\/\/.*">|http:\/\/.*" data-size/)[0];
    linkMatch = linkMatch.replace(/" r|" data-size|">/, "");
    var textMatch = paragraphString.match(/resource=".*" data-size/)[0];
    textMatch = textMatch.replace(/resource="|" data-size/g, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: linkMatch, style: "linkBody"};
    return returnObject;
}

/* Generate link for Metropolitan Museum elements */
function generateMetImageLinkElement(paragraphString) {
    var linkMatch = paragraphString.match(/http:\/\/.*"|http:\/\/.*" r/)[0];
    linkMatch = linkMatch.replace(/" r|"/, "");
    var textMatch = paragraphString.match(/resource=".*" data-size/)[0];
    textMatch = textMatch.replace(/resource="|" data-size/g, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: linkMatch, style: "linkBody"};
    return returnObject;
}

/* Generate link for Vimeo elements */
function generateVimeoLinkElement(paragraphString) {
    var hrefMatch = paragraphString.match(/href=".*"/)[0];
    var textMatch = paragraphString.match(/resource=".*"/)[0];
    textMatch = textMatch.replace(/resource="/, "");
    textMatch = textMatch.replace(/".*/, "");
    hrefMatch = hrefMatch.replace(/href="|"/g, "");
    textMatch = fixUpText(textMatch);
    var returnObject = {text: textMatch, link: hrefMatch, style: "linkBody"};
    return returnObject;
}

/* Depending on what we can do, either generate a link element to for a Scalar
   image, or actually create the image and create an image element for the pdf
   I really don't want to make an image out of this */
function generateScalarImage(paragraphString) {
    // For now, just do the link
    var linkMatch = paragraphString.match(/http:\/\/.*"/)[0];
    linkMatch = linkMatch.slice(0, linkMatch.length - 1);
    // Try to see if we can generate the image from where the image is from
    // Put getBase64 here instead
    getBase64FromImageUrl(linkMatch, function(val) {
        globalURL = val;
        alert(val);
    });
    console.log(globalURL);
    // alert(globalURL);
    // var modifiedLinkMatch = linkMatch.replace("http://", "");
    var returnObject = {
                        image: globalURL, 
                        width: 50,
                        height: 50
                       };
    //var returnObject = {text: "Image Link", link: linkMatch, style: "linkBody"};
    return returnObject;
}

/* For all other links, generate links from this function */
function generateLinkParagraphElement(paragraphString) {
    var linkString = paragraphString.match(/http:.*">/)[0];
    linkString = linkString.replace(/">/, "");
    var textString = paragraphString.match(/>.*/)[0];
    textString = textString.slice(1, textString.length);
    if (linkString.includes("youtube.com") && linkString.includes("/v/")) {
        linkString = linkString.replace(/v\//, "watch?v=");
        linkString = linkString.replace(/ r.*/, "");
    }
    linkString = linkString.replace(/"/g, "");
    var returnObject = {text: textString, link: linkString, style: "linkBody"};
    return returnObject;
}

/* If both, generate the object to show both */
function generateBothColorParagraphElement(paragraphString) {
    var backgroundColorStringVal = (paragraphString.match(/background-color:#.*;/))[0];
    paragraphString = paragraphString.replace(backgroundColorStringVal, "");
    var colorStringVal = (paragraphString.match(/color:#.*;/))[0];
    var colorNumberVal = colorStringVal.replace(/color:|;/g, "");
    var backgroundColorNumberVal = backgroundColorStringVal.replace(/background-color:|;/g, "");
    paragraphString = paragraphString.replace(/.*>/g, "");
    var pushObject = {text: paragraphString, style: "body", background: backgroundColorNumberVal, color: colorNumberVal};
    return pushObject;
}

/* If there is either background color or text color, generate the object to show that */
function generateColorParagraphElement(paragraphString) {
    var matchValArray;
    var matchVal;
    var removeString;
    var isBackgroundColor = false;
    if (paragraphString.includes("background-color")) {
        isBackgroundColor = true;
        matchValArray = paragraphString.match(/background-color:#.*;/);
        removeString = ' style="background-color:#';
    } else {
        matchValArray = paragraphString.match(/color:#.*;/);
        removeString = ' style="color:#';
    }
    matchVal = matchValArray[0];
    var matchValRegex = /color:|;/g;
    matchVal = matchVal.replace(matchValRegex, "");
    var removeString = removeString + matchVal + ';"'
    paragraphString = paragraphString.replace(removeString, "");
    paragraphString = paragraphString.replace(">", "");
    var pushObject = {};
    if (isBackgroundColor) {
        matchVal = matchVal.replace("background-", "");

        paragraphString = paragraphString.replace(' style="background-color:'+matchVal+';"', "");
        pushObject = {text: paragraphString, background: matchVal, style: "body"};
    } else {
        paragraphString = paragraphString.replace(' style="color:'+matchVal+';"', "");
        pushObject = {text: paragraphString, color: matchVal, style: "body"};
    }
    return pushObject;
}

/* Split by span tags and link tags. Those that have those elements will be designed around those
   (i.e. any formatting outside of that will be ommitted). Otherwise, create a matrix from the text
   and generate objects from that (mainly because there might be bold, italics, and underline tags) 
   I understand that functionality is limited. Things will be added to this function as time goes on
   and if people need that functionality*/
function analyzeParagraph(paragraphString, siteURL) {
    var splitRegex = /<\/span><\/span>|<span|<\/span>|<a h|<\/a>|<a cl|<a re/g;
    paragraphStringMatches = paragraphString.split(splitRegex);
    console.log(paragraphStringMatches);
    var textList = [];
    var pushObject = {};
    for (var i = 0; i < paragraphStringMatches.length; i++) {
        if (paragraphStringMatches[i].includes('ass="') && ((paragraphStringMatches[i].includes('youtube.com')) || (paragraphStringMatches[i].includes('youtube.googleapis.com'))) && !(paragraphStringMatches[i].includes('archive.org'))) {
            pushObject = generateYoutubeLinkElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('ass="') && (paragraphStringMatches[i].includes('archive.org'))) {
            pushObject = generateInternetArchiveLinkElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('ass="') && (paragraphStringMatches[i].includes('images.metmuseum.org'))) {
            pushObject = generateMetImageLinkElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('name="') && (paragraphStringMatches[i].includes('vimeo.com'))) {
            pushObject = generateVimeoLinkElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('name="') && (paragraphStringMatches[i].includes('hidvl.nyu.edu'))) {
            pushObject = generateNYUElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('name="') && (paragraphStringMatches[i].includes('ctda.library.miami.edu'))) {
            pushObject = generateCTDAElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('name="') && (paragraphStringMatches[i].includes('videos.criticalcommons.org'))) {
            pushObject = generateCriticalCommonsVideoLinkElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes('name="') && (paragraphStringMatches[i].includes(siteURL))) {
            pushObject = generateScalarImage(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i] === "") {
            continue;
        } else if (paragraphStringMatches[i].includes('ref="') || paragraphStringMatches[i].includes('source="')) {
            paragraphStringMatches[i] = paragraphStringMatches[i].replace('ref="', "");
            pushObject = generateLinkParagraphElement(paragraphStringMatches[i]);
            textList.push(pushObject);
        } else if (paragraphStringMatches[i].includes("style")) {
            var spanMatch = paragraphStringMatches[i].match(/ style=.*>/);
            var spanMatchString = spanMatch[0];
            if (spanMatchString === paragraphStringMatches[i]) {
                var nextVal = paragraphStringMatches[i+1];
                paragraphStringMatches[i] = paragraphStringMatches[i] + nextVal
                paragraphStringMatches.splice(i+1, 1);
                pushObject = generateBothColorParagraphElement(paragraphStringMatches[i]);
            } else {
                var newString = paragraphStringMatches[i].replace(spanMatchString, "");
                pushObject = generateColorParagraphElement(paragraphStringMatches[i]);
            }


            textList.push(pushObject);
        } else {
            var analyzedTextList = parseParagraph(paragraphStringMatches[i]);
            for (var m = 0; m < analyzedTextList.length; m++) {
                analyzedTextList[m] = parseParagraph(analyzedTextList[m]);
            }
            for (var m = 0; m < analyzedTextList.length; m++) {
                for (var n = 0; n < analyzedTextList[m].length; n++) {
                    analyzedTextList[m][n] = parseParagraph(analyzedTextList[m][n]);
                }
            }
            var objectFromMatrix = generateObjectsFromParagraphMatrix(analyzedTextList);
            if (objectFromMatrix.length === 0) {
                continue;
            }
            textList = textList.concat(objectFromMatrix);
        }
    }
    var returnObject = {text: textList, style: "body"};
    return returnObject;
}

/* Much like dealing with paragraphs, generate a 3D matrix from the text, then 
   run it through generateObjectsFromParagraphMatrix to get all the appropriate objects */
function analyzeListContent(listTextVal) {
    var analyzedTextList = parseParagraph(listTextVal);
    for (var m = 0; m < analyzedTextList.length; m++) {
        analyzedTextList[m] = parseParagraph(analyzedTextList[m]);
    }
    for (var m = 0; m < analyzedTextList.length; m++) {
        for (var n = 0; n < analyzedTextList[m].length; n++) {
            analyzedTextList[m][n] = parseParagraph(analyzedTextList[m][n]);
        }
    }
    var objectFromMatrix = generateObjectsFromParagraphMatrix(analyzedTextList);
    return objectFromMatrix;
}

/* Determine what type of list it is, and generate the list accordingly */
function generateListObject(tagString, tagType) {
    splitArray = tagString.split(/<\/li>/);
    var listObject = {};
    if (tagType === "ul") {
        listObject = {
            ul: []
        };
    } else {
        listObject = {
            ol: []
        };
    }
    for (var i = 0; i < splitArray.length; i++) {
        if (splitArray[i] === "") {
            continue;
        } else {
            // For now, filter out span related stuff (I'll worry about it at another time)
            splitArray[i] = splitArray[i].replace(/<\/span>|<span style=.*">|<li>/g, "");
            var listTextList = analyzeListContent(splitArray[i]);
            var listObjVal = {text: listTextList, style: "listVal"};
            if (tagType === "ul") {
                listObject["ul"] = listObject["ul"].concat(listObjVal);
            } else {
                listObject["ol"] = listObject["ol"].concat(listObjVal);
            }

        }
    }
    return listObject;
};
/* Filter out unnecessary text and generate an object for blockquotes*/
function generateBlockQuoteObject(tagString) {
    var blockQuoteRegex = /quote>/g;
    tagString = tagString.replace(blockQuoteRegex, '');
    var paragraphRegex = /<p>|<\/p>/;
    blockQuoteMatches = tagString.split(paragraphRegex);
    var blockObjectList = [];
    var pushObject = {};
    for (var i = 0; i < blockQuoteMatches.length; i++) {
        if (blockQuoteMatches[i] === "") {
            continue;
        } else {
            pushObject = {text: blockQuoteMatches[i], style: "quoteFormat"};
            blockObjectList.push(pushObject);
        }
    }
    return blockObjectList;
};

/* Filter out unnecessary text and generate an object for preformatted text*/
function generatePreObject(tagString) {
    var regex = /&nbsp;|<pre>|<\/pre>/g;
    tagString = tagString.replace(regex, '');
    tagStringValArray = tagString.split("\n");
    returnList = [];
    for (var i = 0; i < tagStringValArray.length; i++) {
        var pushObject = {text: tagStringValArray[i], style: "preFormat"};
        returnList.push(pushObject);
    }
    return returnList;
};

/* From the split content array, see what is a paragraph, what is a header,
   what is a preformatted section, what is a blockquote, and what is a list */
function filterContent(contentArray, siteURL) {
    var contentList = [];
    var pushObject = {};
    for (var i = 0; i < contentArray.length; i++) {
        contentArray[i] = contentArray[i].replace(/&nbsp;/g, "");
        if (contentArray[i].includes("1>")) {
            contentArray[i] = contentArray[i].replace("1>", "");
            pushObject = {text: contentArray[i], style: "h1"};
            contentList.push(pushObject);
        } else if (contentArray[i].includes("2>")) {
            contentArray[i] = contentArray[i].replace("2>", "");
            pushObject = {text: contentArray[i], style: "h2"};
            contentList.push(pushObject);
        } else if (contentArray[i].includes("3>")) {
            contentArray[i] = contentArray[i].replace("3>", "");
            pushObject = {text: contentArray[i], style: "h3"};
            contentList.push(pushObject);
        } else if (contentArray[i].includes("4>")) {
            contentArray[i] = contentArray[i].replace("4>", "");
            pushObject = {text: contentArray[i], style: "h4"};
            contentList.push(pushObject);
        } else if (contentArray[i].includes("5>")) {
            contentArray[i] = contentArray[i].replace("5>", "");
            pushObject = {text: contentArray[i], style: "h5"};
            contentList.push(pushObject);
        } else if (contentArray[i].includes("<li>")) {
            if (contentArray[i].includes("</o")) {
                contentArray[i] = contentArray[i].replace("</o", "");
                pushObject = generateListObject(contentArray[i], 'ol');
            } else {
                contentArray[i] = contentArray[i].replace("list>", "");
                contentArray[i] = contentArray[i].replace("</u", "");
                pushObject = generateListObject(contentArray[i], 'ul');
            }
            contentList.push(pushObject);

        } else if (contentArray[i] === "") {
            continue;
        } else if (contentArray[i].includes("quote>")) {
            var pushObjectList = generateBlockQuoteObject(contentArray[i]);
            contentList = contentList.concat(pushObjectList);
        } else if (contentArray[i].includes("<pre>")) {
            var pushObjectList = generatePreObject(contentArray[i]);
            contentList = contentList.concat(pushObjectList);
        } else {
            if (contentArray[i].includes("<strong>") || contentArray[i].includes("<em>") || contentArray[i].includes("<u>") || contentArray[i].includes("<span style") || contentArray[i].includes("<a href") || contentArray[i].includes("<a class") || contentArray[i].includes("<a name") || contentArray[i].includes("<a resource")) {
                pushObject = analyzeParagraph(contentArray[i], siteURL);
                contentList.push(pushObject);
            } else {
                pushObject = {text: contentArray[i], style: "body"};
                contentList.push(pushObject);
            }
        }
        var spacingObject = { text: " ", style: "spacing"};
        contentList.push(spacingObject);
    }
    return contentList;
};

/* Access the JSON file. Get the description and the content.
   Once the content has been retrieved, split it up by list, header, and break
   tags and run it through a function to analyze that 
   After that, generate the PDF from the objects */
function convert(JSONObj, authorName, siteURL, titleVal) {
    var JSONString = JSON.stringify(JSONObj);
    var linkString = siteURL + titleVal + "/index.";
    var matchNumber = JSONString.match(/\/index\.[0-9]{1,3}/);
    var numberToUse = matchNumber[0].replace(/\/index\./, "");

    // Title, Description, and Content
    var descAndContString = linkString + numberToUse;
    var JSONDescAndCont = JSONObj[descAndContString]; 
    var JSONDescriptionList = JSONDescAndCont["http://purl.org/dc/terms/description"];

    var JSONContentList = JSONDescAndCont["http://rdfs.org/sioc/ns#content"];
    var descriptionIntermediary = JSONDescriptionList[0];
    var contentIntermediary = JSONContentList[0];
    var description = descriptionIntermediary["value"];
    description = filterOut(description);
    var content = contentIntermediary["value"];
    content = content.replace(/<ul>/g, "<unlist>");
    var contentParagraphMatches = content.split(/<br \/>|<ol>|<un|l>|<\/h1>|<\/h2>|<\/h3>|<\/h4>|<\/h5>|<\/pre>|<block|<\/blockquote>|<h/);
    var titleList = JSONDescAndCont["http://purl.org/dc/terms/title"];
    var titleIntermediary = titleList[0];
    var title = titleIntermediary["value"];
    var contentList = filterContent(contentParagraphMatches, siteURL);
    var docDef = {
        pageSize: 'LETTER',

        pageMargins: [ 50, 50, 50, 50 ],

        footer: function(currentPage, pageCount) { 
          return "Page: " + currentPage.toString() + ' of ' + pageCount; 
        },

        content: [
            { text: authorName, style: "name" }, 
            { text: " ", style: "spacing"},
            { text: title, style: "title" }, 
            { text: " ", style: "spacing"},
            { text: "Description: " + description, style: "description" },
            { text: " ", style: "spacing"} 
            ],

            styles: {
                title: {
                    fontSize: 26,
                    bold: true,
                    alignment: 'center'
                },
                name: {
                    fontSize: 14,
                    bold: true,
                    alignment: 'center'
                },
                description: {
                    fontSize: 12,
                    bold: false,
                    alignment: 'left'
                },
                listVal: {
                    fontSize: 12,
                },
                body: {
                    fontSize: 16,
                    bold: false,
                    alignment: 'left'
                },
                linkBody: {
                    fontSize: 16,
                    underline: true,
                    color: "#99ccff"
                },
                strikethroughBody: {
                    fontSize: 16,
                    bold: false,
                    decoration: 'lineThrough',
                    alignment: 'left'
                },
                spacing: {
                    fontSize: 6,
                    alignment: 'center'
                },
                preFormat: {
                    fontSize: 10,
                    alignment: 'center',
                    bold: false
                },
                quoteFormat: {
                    fontSize: 12,
                    alignment: 'center',
                    bold: true
                },
                h1: {
                    fontSize: 20,
                    alignment: 'left',
                    bold: true
                },
                h2: {
                    fontSize: 18,
                    alignment: 'left',
                    bold: true
                },
                h3: {
                    fontSize: 16,
                    alignment: 'left',
                    bold: true
                },
                h4: {
                    fontSize: 14,
                    alignment: 'left',
                    bold: true
                },
                h5: {
                    fontSize: 12,
                    alignment: 'left',
                    bold: true
                },
                h6: {
                    fontSize: 10,
                    alignment: 'left',
                    bold: true
                }
            }
        };

    // Create the paragraphs
    for (var m = 0; m < contentList.length; m++) {
        docDef.content.push(contentList[m]);
    }
    pdfMake.createPdf(docDef).download(authorName + ' - ' + 'pdfVal.pdf');
};