function filterContentForParagraphs(contentVal) {
      var paragraphList = [];
      var listString = "";
      for (var i = 0; i < contentVal.length; i++) {
        if (contentVal.substring(i, i+6) == "<br />") {
          paragraphList.push(listString);
          listString = "";
        } else {
          listString = listString + contentVal.charAt(i);
        }
      }
      return paragraphList;
};

function convert(JSONString) {
	/* Eventually, once this is connected to the
	 * website, use jQUERY to get the JSON file
	 * if possible, the parse.
	 * For right now, feed in a string version
	 * of the JSON object, parse through it,
	 * extract the content, title, author, metadata,
	 * citations, description, and so on.
	 * Then form a PDF out of it.
	*/
	var JSONObj = JSON.parse(JSONString);
	// Sooner or later, make this more general
	// Keep everything up until the / after the scalar
	// then everything afte the title
	var JSONDescAndCont = JSONObj["http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.2"];
	//console.log(JSON.stringify(JSONDescAndCont));
	var JSONDescriptionList = JSONDescAndCont["http://purl.org/dc/terms/description"];
	var JSONContentList = JSONDescAndCont["http://rdfs.org/sioc/ns#content"];
  	var descriptionIntermediary = JSONDescriptionList[0];
  	var contentIntermediary = JSONContentList[0];
  	var description = descriptionIntermediary["value"];
  	var content = contentIntermediary["value"];
  	//console.log(description);
	var titleList = JSONDescAndCont["http://purl.org/dc/terms/title"];
	var titleIntermediary = titleList[0];
	var title = titleIntermediary["value"];
	//console.log(title);

  var docDef = {
    content: [
      // ACTUALLY GET THE NAME 
      {text: "Mr. Chungus", style: "name"},
      {text: title, style: "title"},
      {text: "Description" + description, style: "description"},
      {text: content, style: "body"}
    ],

    style: {
      title: {
        fontSize: 22,
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
      body : {
        fontSize: 16,
        bold: false,
        alignment: 'left'
      }
    }
  };
  pdfMake.createPdf(docDef).open();
}

// Test Cases
var JSONStringTestVal = `{
  "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index" : {
    "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" : [
      { "value" : "http://scalar.usc.edu/2012/01/scalar-ns#Composite", "type" : "uri" }
    ],
    "http://scalar.usc.edu/2012/01/scalar-ns#isLive" : [
      { "value" : "1", "type" : "literal" }
    ],
    "http://www.w3.org/ns/prov#wasAttributedTo" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/users/8", "type" : "uri" }
    ],
    "http://purl.org/dc/terms/created" : [
      { "value" : "2017-05-24T11:09:10-05:00", "type" : "literal" }
    ],
    "http://scalar.usc.edu/2012/01/scalar-ns#urn" : [
      { "value" : "urn:scalar:content:54", "type" : "uri" }
    ],
    "http://scalar.usc.edu/2012/01/scalar-ns#version" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.2", "type" : "uri" }
    ],
    "http://purl.org/dc/terms/hasVersion" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.2", "type" : "uri" }
    ],
    "http://scalar.usc.edu/2012/01/scalar-ns#citation" : [
      { "value" : "method=instancesof/content;methodNumNodes=1;", "type" : "literal" }
    ]
  },

  "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.2" : {
    "http://open.vocab.org/terms/versionnumber" : [
      { "value" : "2", "type" : "literal" }
    ],
    "http://purl.org/dc/terms/title" : [
      { "value" : "Test", "type" : "literal" }
    ],
    "http://purl.org/dc/terms/description" : [
      { "value" : "To be, or not to be, that is the question: Whether 'tis nobler in the mind to suffer The slings and arrows of outrageous fortune, Or to take Arms against a Sea of troubles, And by opposing end them: to die, to sleep No more; and by a sleep, to say we end the heart-ache, and the thousand natural shocks that Flesh is heir to?", "type" : "literal" }
    ],
    "http://rdfs.org/sioc/ns#content" : [
      { "value" : "Lorem ipsum dolor sit amet, modo noster aliquid an mea. Eu vim nominati democritum, has quando iisque cu. Id eos falli corrumpit disputando, mea ex vide delectus. Postea vocibus at vix, nullam forensibus est eu. Pri eu brute fuisset noluisse, ut vix assum complectitur. Nibh everti efficiantur ut duo, eum tale agam delicatissimi eu.<br /><br />Est te sumo epicuri, ei vis exerci nonumes, ea mazim graece integre usu. Debet animal officiis nec te, nec atqui persius fuisset eu. Vel assum everti sententiae an. Diceret detracto voluptaria cu usu. At vis diam civibus suscipiantur.<br /><br />At reque munere eam, ad evertitur comprehensam has. Pri altera labore in. Ius ea oratio dissentiunt. His ad etiam iudicabit, vim id falli nulla. Rebum nostro facilis ex sea, in enim volumus mel, ea vim tota legendos. Qui te nibh quodsi persequeris, cu viderer tacimates rationibus cum.<br /><br />Etiam aperiam cum ei, pri oratio tamquam tacimates eu, erant omittam ex eos. Te his eros senserit. Dicunt inermis per ea, pri at vero mucius oportere. Delicata democritum in eum, novum voluptaria per ex.<br /><br />Velit sanctus suscipit ad vix, tibique assueverit referrentur ex his. Amet vocent te mei, vis sapientem disputando et. Ea has lorem utroque consulatu, cu quis dico quo. Ne est modus vivendo dissentias, per in atqui mucius noster, maiorum habemus eum an. No mei veniam prompta molestiae, per no quodsi minimum mentitum. Ad quod minim posidonium duo, legere eruditi habemus an vim, mel quod tibique ei.", "type" : "literal" }
    ],
    "http://scalar.usc.edu/2012/01/scalar-ns#defaultView" : [
      { "value" : "plain", "type" : "literal" }
    ],
    "http://www.w3.org/ns/prov#wasAttributedTo" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/users/8", "type" : "uri" }
    ],
    "http://purl.org/dc/terms/created" : [
      { "value" : "2017-05-24T13:30:26-05:00", "type" : "literal" }
    ],
    "http://scalar.usc.edu/2012/01/scalar-ns#urn" : [
      { "value" : "urn:scalar:version:130", "type" : "uri" }
    ],
    "http://purl.org/dc/terms/isVersionOf" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index", "type" : "uri" }
    ],
    "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" : [
      { "value" : "http://scalar.usc.edu/2012/01/scalar-ns#Version", "type" : "uri" }
    ]
  },

  "urn:scalar:tag:130:130" : {
    "http://scalar.usc.edu/2012/01/scalar-ns#urn" : [
      { "value" : "urn:scalar:tag:130:130", "type" : "uri" }
    ],
    "http://www.openannotation.org/ns/hasBody" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.2", "type" : "uri" }
    ],
    "http://www.openannotation.org/ns/hasTarget" : [
      { "value" : "http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.2", "type" : "uri" }
    ],
    "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" : [
      { "value" : "http://www.openannotation.org/ns/Annotation", "type" : "uri" }
    ]
  } 
}`;
convert(JSONStringTestVal);