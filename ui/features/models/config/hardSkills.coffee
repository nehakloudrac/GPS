angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'hardSkills', [
      label: "Accounting/budgeting"
      key: 'accounting'
    ,
      label: "Client management"
      key: 'clientManagement'
    ,
      label: "Contract negotiation"
      key: 'contractNegotiation'
    ,
      label: "Event planning"
      key: 'eventPlanning'
    ,
      label: "Financial analysis"
      key: 'financialAnalysis'
    ,
      label: "Fundraising"
      key: 'fundraising'
    ,
      label: "Marketing/advertising"
      key: 'marketing'
    ,
      label: "Project management"
      key: 'projectManagement'
    ,
      label: "Proposal/report writing"
      key: 'reportWriting'
    ,
      label: "Public relations"
      key: 'publicRelations'
    ,
      label: "Public speaking"
      key: 'publicSpeaking'
    ,
      label: "Research and analysis"
      key: 'research'
    ,
      label: "Staff management"
      key: 'staffManagement'
    ,
      label: "Social media outreach"
      key: 'socialMedia'
    ,
      label: "Written communication"
      key: 'writtenCommunication'
  ]
