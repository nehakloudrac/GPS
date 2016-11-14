angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'countrySliders', [
      key: 'cultureFamiliarity'
      label: 'Cultural Familiarity'
      prompt: "How familiar are you with the local culture?"
      instructions: "Use the slider to rank your familiarity with the local culture."
      valueDescriptions: [
        "I can behave appropriately in common tourist situations.  I am familiar with basic cultural conventions that are common to the region."
        "I can describe the basics of the history, politics, geography, and economy of the country in an educated, but non-expert, manner. I can recognize basic cultural differences, but I am uncertain about how I should behave."
        "I can intelligently discuss the history, politics, geography, and economy of the country. I can travel around the country independently and behave appropriately in most social situations, both with occasional cultural missteps."
        "I can objectively discuss the differences between my culture and the local culture.  I am confident about how to behave in the local culture."
        "I can provide insight and commentary on the culture, history, politics, and economy of the country.  I am completely comfortable within the culture."
        "I can provide in-depth analysis on all topics in level 5 while integrating popular references and cultural nuance.  I can seamlessly navigate the culture like a 'local' with the ability to understand and communicate nuance - subtlety, humor, etc."
      ]
    ,
      key: 'businessFamiliarity'
      label: 'Local Business Familiarity'
      prompt: "How familiar are you with the local business culture?"
      instructions: "Use the slider to rank your familiarity with local business practices."
      valueDescriptions: [
        "I have no experience with how local business is conducted relative to my home country.  I can describe the country's economic state and their primary industries."
        "I can identify some of the obvious differences between local business practices and those within my home country, but I am uncertain about how to comfortably navigate them."
        "I can understand the basics of local business practices and can work in-country with supervision, with the occasional cultural misstep."
        "I am familiar with local business practices and can operate within them with minimal supervision."
        "I have in-depth understanding of local business practices and can operate without supervision.  I can conduct business - advise and strategize - within the local organization."
        "I can thoroughly navigate local business practices - extrapolating and interpolating where necessary to maximize achievement.  I can independently manage a local business unit as the senior leader."
      ]
  ]
