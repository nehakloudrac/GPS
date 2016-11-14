angular.module 'gps.common.models'
.config (configProvider) ->
  certs = {
    ilr:
      label: "ILR"
      valueLabels: ['0','0+','1','1+','2','2+','3','3+','4','4+','5']
      fieldOrder: [['reading','listening'],['speaking','writing']]
      fields: [
          key: 'reading'
          label: 'Reading'
        ,
          key: 'listening'
          label: 'Listening'
        ,
          key: 'speaking'
          label: 'Speaking'
        ,
          key: 'writing'
          label: 'Writing'
      ]
    actfl:
      label: "ACTFL"
      valueLabels: ['Novice - Low','Novice - Mid','Novice - High','Intermediate - Low','Intermediate - Mid','Intermediate - High','Advanced','Advanced Plus','Superior','Distinguished']
      fieldOrder: [['reading','listening'],['speaking','writing']]
      fields: [
          key: 'reading'
          label: 'Reading'
        ,
          key: 'listening'
          label: 'Listening'
        ,
          key: 'speaking'
          label: 'Speaking'
        ,
          key: 'writing'
          label: 'Writing'
      ]
    cefr:
      label: "CEFR"
      valueLabels: ['A1','A2','B1','B2','C1','C2']
      fieldOrder: [['reading','listening'],['writing', 'spokenInteraction'], ['spokenProduction']]
      fields: [
          key: 'reading'
          label: 'Reading'
        ,
          key: 'listening'
          label: 'Listening'
        ,
          key: 'writing'
          label: 'Writing'
        ,
          key: 'spokenInteraction'
          label: 'Spoken Interaction'
        ,
          key: 'spokenProduction'
          label: 'Spoken Production'
      ]
    alte:
      label: "ALTE"
      valueLables: ['Breakthrough','Level 1','Level 2','Level 3','Level 4','Level 5']
      fieldOrder: [['reading','writing'],['listeningAndSpeaking']]
      fields: [
          key: 'reading'
          label: 'Reading'
        ,
          key: 'writing'
          label: 'Writing'
        ,
          key: 'listeningAndSpeaking'
          label: 'Listening and Speaking'
      ]
    other:
      label: "Other"
      valueLabels: ['Novice', 'Limited', 'Intermediate', 'Advanced', 'Professional', 'Native/Bilingual']
      fieldOrder: [['reading','listening'],['speaking','writing']]
      fields: [
          key: 'reading'
          label: 'Reading'
        ,
          key: 'listening'
          label: 'Listening'
        ,
          key: 'speaking'
          label: 'Speaking'
        ,
          key: 'writing'
          label: 'Writing'
      ]
    gps:
      label: "GPS Self Assessment"
      valueLabels: ['Novice', 'Limited', 'Intermediate', 'Advanced', 'Professional', 'Native/Bilingual']
      valueDescriptions: []
      fieldOrder: [['reading','writing'],['listening','interacting']]
      fields: [
          key: 'listening'
          label: 'Listening'
          descriptors: [
            ["I can only occasionally recognize a familiar word, place, or name."]
            ["I can glean the general idea from a TV/radio broadcast on a familiar topic."]
            ["I can comprehend the main idea and key details about a news story or presentation topic — “who, what, where, when, and why”. I cannot grasp technical content."]
            ["I can comprehend an opinion and the supporting argument, often detecting attitude and feeling."]
            ["I can readily comprehend what I hear on TV/radio and in live presentations, including technical detail and most nuance."]
            ["I can comprehend detailed debate and discussion—technical and otherwise— including all colloquialisms, historical references, and nuance."]
          ]
        ,
          key: 'reading'
          label: 'Reading'
          descriptors: [
            ["I can recognize a few common words or phrases but use a translation tool for comprehension."]
            ["I can read and comprehend simple instructions/directions and short messages on familiar topics (e.g. brief emails or social media posts)."]
            ["I can read and comprehend the main point of most short articles, letters, or emails, but not nuance."]
            ["I can read and comprehend technical or business writing with minimal difficulty."]
            ["I can read and comprehend almost anything, including technical jargon and colloquialisms."]
            ["I can read and comprehend anything, detecting irony and humor, including a range of local and national dialects."]
          ]
        ,
          key: 'writing'
          label: 'Writing'
          descriptors: [
            ["I am unable to write in the local language."]
            ["I can incorporate a few phrases and popular references in the local language when writing in my native language."]
            ["I can write basic correpondence or a brief report on a familiar topic in the local language."]
            ["I can write research and data reports in my area of expertise for an internal audience.", "I can write short reports or correspondence for an external audience, with supervision."]
            ["I can write official correspondenc and complex reports in my area of expertise for both internal and external use or publication.", "I can draft marketing materials and/or press releases."]
            ["I can write and edit complex material in both formal and colloquial style, undistinguishable from a native speaker."]
          ]
        ,
          key: 'interacting'
          label: 'Speaking'
          descriptors: [
            ["I can say basic greetings and a few numbers."]
            ["I can ask and answer general questions on familiar topics—family, work, residence."]
            ["I can ask and provide detailed information on familiar topics—family, work, interests."]
            ["I can hold a conversation about diverse topics, with minimal mistakes."]
            ["I can handle complex conversations with ease, including business transactions and negotiations, using high-level technical language."]
            ["I can participate in detailed debate—incorporating a broad vocabulary base, colloquial terminology, and nuance.", "I can participate in conversations in local and regional dialects."]
          ]
      ]
  }
  
  for key,cert of certs
    cert.key = key
  
  configProvider.set 'languageCertifications', certs
