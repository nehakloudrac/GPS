<?php

final class Values
{
    // random stuff
    public static $industries = ['Education services','Biotech', 'Medical','Video games','Financial services'];

    // user stuff
    public static $genders = ["male","female","other","decline"];
    public static $usWorkAuths = ["citizen","green_card","h1b","tn"];
    public static $usSecClearances = ["no","yes","confidential","secret","top_secret","polygraph"];
    public static $jobStatuses = ["unemployed", "looking", "open", "satisfied", "happy"];
    public static $instReferrers = ['iie','aatsp','npca'];
    public static $referralMediums = ["search","linkedin","facebook","twitter","career_service","program_lang","program_study_abroad","program_volunteer","other"];
    public static $diversity = ["eth_asn_pacific","eth_african_american","eth_hispanic","eth_american_indian","lgbtq","disabled","veteran"];
    // general profile stuff
    public static $softSkills = ["adaptability","communication","creativity","critical_thinking","decision_making","curiosity","leadership","problem_solving","resiliency","motivation","teamwork","tolerate_ambiguity"];
    public static $employerIdeals = ["accountability","creativity","customer_satisfaction","diversity","integrity","meritocracy","professional_growth","productivity","quality","family"];
    public static $characterTraits = ["ambitious","analytical","collaborative","competitive","confident","creative","dependable","detail_oriented","enthusiastic","entrepreneurial","flexible","hard_working","independent","logical","loyal","organized","handles_pressure","risk_taker","self_aware","self_starter"];
    
    // ideal job stuff
    public static $idealJobTypes = ["full_time","part_time","project","internship"];
    public static $idealJobEmployerTypes = ["company_private","company_public","gov_enterprise","non_profit","edu","foundation","org_intl","gov_fed","gov_local","other"];
    public static $payStatus = ["paid","unpaid"];
    public static $willingnessToTravel = ["occaisionally","up_to_25","up_to_50","over_50","no"];
    public static $instTypes = ["company_private","company_public","gov_enterprise","non_profit","edu","foundation","org_intl","gov_fed","gov_local","other"];    
    
    // country stuff
    public static $countryPurposes = ["work","volunteer","military","study","teaching","dependant","research"];
    public static $countryCodes = ['US','RU','CH','BR'];
    
    // lang stuff
    public static $langCodes = ['rus','eng','arb','per'];
    public static $langMacroCodes = ['ara'];
    
    // timeline stuff
    public static $academicSubjects = ['Accounting','Physics','Biology','International Studies'];
    public static $degrees = ["associates","bachelors","masters","mba","jd","phd","md","edd","none"];
    public static $jobTypes = ["full_time","part_time","internship","project"];
    public static $langSources = ["self_study","community","training","school"];
    public static $milBranches = ["us_navy","us_army","us_marines","us_air_force","us_coast_guard"];
    public static $milGeoSpecs = ["USAFRICOM","USCENTCOM","USEUCOM","USNORTHCOM","USPACOM","USSOUTHCOM"];
    public static $milRankTypes = ["enlisted","officer"];
    
    // WARNING: db isn't strictly validating this one
    public static $positionLevels = ["president_ceo","owner_founder","principal","cxo","vp","director","manager","advanced","entry","intern"];

    public static $researchLevels = ["undergrad","postgrad","grad_student","postdoc_fellow","professional"];
    public static $volunteerStatus = ["part_time","full_time"];
    public static $concentrationTypes = ["major","minor"];
    
    // other bits for convenience
    public static $countryActivityProperties = ["attendClasses","attendClassesLocalLang","modifyCurriculum","modifyCurriculumLocalLang","taughtLocals","taughtLocalsLocalLang","commandUnit","commandUnitLocalLang","persuadeLocalsProduct","persuadeLocalsProductLocalLang","relationshipsLocalGov","relationshipsLocalGovLocalLang","collaborateWithLocals","collaborateWithLocalsLocalLang","interfaceWithProfessionals","interfaceWithProfessionalsLocalLang","negotiateContracts","negotiateContractsLocalLang","manageTeam","manageTeamLocalLang","navLegalReqs","navLegalReqsLocalLang","navFinancialReqs","navFinancialReqsLocalLang","accommodateValues","accommodateValuesLocalLang","professionalInterpretation","professionalInterpretationLocalLang","professionalTranslation","professionalTranslationLocalLang","localTradition","localTraditionLocalLang","convinceLocals","convinceLocalsLocalLang","talkToMedia","talkToMediaLocalLang","publicPresentations","publicPresentationsLocalLang","navLocalRegs","navLocalRegsLocalLang","performanceConsequences","performanceConsequencesLocalLang","leadProject","leadProjectLocalLang","socializeWithLocals","socializeWithLocalsLocalLang","useSocialMedia","useSocialMediaLocalLang","writeReports","writeReportsLocalLang"];
    public static $hardSkillProperties = ['accounting','clientManagement','contractNegotiation','eventPlanning','financialAnalysis','fundraising','marketing','projectManagement','reportWriting','publicRelations','publicSpeaking','research','staffManagement','socialMedia','writtenCommunication'];
    public static $idealJobPrefProperties = ['workWithTeam','workInField','travel','multiTask','workWithCustomers','fewerRules','takeRisks','measureAgainstOthers','socializeOneToOne','newEnvironments','compete','commission','multicultural'];
}
