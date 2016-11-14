###
# These are ordered by most specific to least specific so that more specific
# sliders will show first in the interface.
###
angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'countryPurposeSliders', [
      ###
      ## Study only ##
      ###

      purposes: ['study']
      key: 'attendClasses'
      label: "Attended classes with local residents"
    ,

      ###
      ## Teach only ##
      ###

      purposes: ['teach']
      key: 'modifyCurriculum'
      label: "Modified my curriculum to be more culturally appropriate"
    ,
      purposes: ['teach']
      key: 'taughtLocals'
      label: "Taught local students"
    ,

      ###
      ## Military only ##
      ###

      purposes: ['military']
      key: 'commandUnit'
      label: "Served as the commander of a unit"
    ,

      ###
      ## Work ##
      ###

      purposes: ['work']
      key: 'persuadeLocalsProduct'
      label: "Sold products/services"
    ,

      ###
      ## Multiple purposes ##
      ###

      purposes: ['work','volunteer','military']
      key: 'relationshipsLocalGov'
      label: "Built relationships with local government officials"
    ,
      purposes: ['work','volunteer','military','teach','research']
      key: 'collaborateWithLocals'
      label: "Collaborated with local residents on specific projects"
    ,
      purposes: ['work','volunteer','military','teach']
      key: 'interfaceWithProfessionals'
      label: "Managed local client/partner relationships"
    ,
      purposes: ['work','volunteer','military','teach']
      key: 'negotiateContracts'
      label: "Negotiated terms or contracts with local residents"
    ,
      purposes: ['work','volunteer','teach','military']
      key: 'manageTeam'
      label: "Supervised a team of local residents"
    ,
      purposes: ['work','volunteer','military','teach']
      key: 'navLegalReqs'
      label: "Dealt with country-specific or international legal requirements"
    ,
      purposes: ['work','volunteer','military','teach']
      key: 'navFinancialReqs'
      label: "Dealt with country-specific or international tax or accounting requirements"
    ,
      purposes: ['work','volunteer','research']
      key: 'leadProject'
      label: "Served as the team leader on a project or program"
    ,

      ###
      # Below this are generic - showing regardless of listed purposes
      ###

      localLang: false
      purposes: null
      key: 'professionalInterpretation'
      label: "Acted as an interpreter (speaking)"
    ,
      localLang: false
      purposes: null
      key: 'professionalTranslation'
      label: "Acted as a translator (writing)"
    ,
      purposes: null
      key: 'localTradition'
      label: "Participated in local traditions or cultural ceremonies"
    ,
      purposes: null
      key: 'convinceLocals'
      label: "Influenced local residents to try something new"
    ,
      purposes: null
      key: 'talkToMedia'
      label: "Interacted with the media"
    ,
      purposes: null
      key: 'publicPresentations'
      label: "Made public presentations"
    ,
      purposes: null
      key: 'navLocalRegs'
      label: "Navigated local government regulations"
    ,
      purposes: null
      key: 'performanceConsequences'
      label: "Participated in projects or activities that had a positive impact in the local community"
    ,
      purposes: null
      key: 'socializeWithLocals'
      label: "Socialized with local residents"
    ,
      purposes: null
      key: 'useSocialMedia'
      label: "Used local social media channels"
    ,
      purposes: null
      key: 'writeReports'
      label: "Wrote reports or proposals that included a local context"
  ]
