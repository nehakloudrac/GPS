// fill collection with resources, scanning some fields to ensure no dups are added
var currentDate = new ISODate(); //mongo current date

// hard-coded content to add, parted from config files for the most part
var resources = [
  // articles hard-coded from old resource section
  {type: 'article', mediaType: 'text', title: 'Do you see what I see?', url: 'http://www.economist.com/news/science-and-technology/21652258-children-exposed-several-languages-are-better-seeing-through-others-eyes-do', description: "Bilingual children, and also those simply exposed to another language on a regular basis, have an edge at the business of getting inside others' minds."},
  {type: 'article', mediaType: 'text', title: 'Foreign Language Policies:  Is Everyone Really Speaking English?', url: 'http://blogs.edweek.org/edweek/global_learning/2015/09/foreign_language_policies_around_the_world_is_everyone_else_really_speaking_english.html', description: "75% of the world does not speak English.  Iran and iraque ae two of the lowest ranking counries on the Education First English proficiency index.  English-dominant countries are far behind in language abilities."},
  {type: 'article', mediaType: 'text', title: 'New research shows bilingual people have yet another advantage', url: 'http://www.businessinsider.com/how-language-changes-views-of-the-world-2015-8', description: "Bilinguals get all the perks. Better job prospects, a cognitive boost and even protection against dementia.\n\nNow new research shows that they can also view the world in different ways depending on the specific language they are operating in."},
  {type: 'article', mediaType: 'text', title: 'What’s Your Language Strategy?', url: 'https://hbr.org/2014/09/whats-your-language-strategy', description: "Language pervades every aspect of organizational life. It touches everything. Yet remarkably, leaders of global organizations, whose employees speak a multitude of languages, often pay too little attention to it in their approach to talent management."},
  {type: 'article', mediaType: 'text', title: 'New study makes the link between study abroad and employability', url: 'http://monitor.icef.com/2014/10/new-study-makes-link-study-abroad-employability/', description: "Amidst a backdrop of considerable concerns around youth employment in many parts of the world, a new European Commission study released this month finds that young people who study or train abroad not only gain knowledge in specific disciplines, but also strengthen key skills highly valued by employers."},

  // quotes, or "facts & figures" hard coded from old resource section
  {type: 'quote', mediaType: 'text', url: 'http://blogs.edweek.org/edweek/global_learning/2015/09/foreign_language_policies_around_the_world_is_everyone_else_really_speaking_english.html', description: "Not everyone is speaking English, and we can't expect them to. There are so many benefits that we are currently missing out on in our monolingual bubble: enhanced business opportunities, smarter kids, stronger national defense, and better communication within our local communities just to name a few. So what do you say, America: can we stop turning a deaf ear to the rest of the world?"},
  {type: 'quote', mediaType: 'text', url: 'http://www.huffingtonpost.com/audrey-j-murrell/moving-from-study-abroad-_b_9212316.html', description: "Skills that define students' employability include not only workplace skills (e.g., problem solving, decision making, conflict resolution) and academic knowledge (e.g., subject matter expertise), but also personal skills (e.g., initiative, integrity) and soft skills (e.g., communication, teamwork)."},
  {type: 'quote', mediaType: 'text', url: 'http://www.hewlett.org/programs/education/deeper-learning/what-deeper-learning', description: "There is a growing emphasis in higher education on deeper learning approaches, defined by the William and Flora Hewlett Foundation as the mastery of content that engages students in critical thinking, problem-solving, collaboration, and self-directed learning."},
  {type: 'quote', mediaType: 'file', url: 'http://resources.rosettastone.com/CDN/us/pdfs/Biz-Public-Sec/Forbes-Insights-Reducing-the-Impact-of-Language-Barriers.pdf', description: "Forbes Insights, in conjunction with Rosetta Stone, surveyed more than 100 executives at large U.S. businesses (annual revenue of more than $500 million) and found that language barriers have a broad and pervasive impact on business operations."},
  {type: 'quote', mediaType: 'text', url: 'http://www.washingtonpost.com/news/on-leadership/wp/2015/07/23/accenture-ceo-explains-the-reasons-why-hes-overhauling-performance-reviews/', description: "For many of our clients, whatever the industry, they are all coming to me saying that their No. 1 challenge is getting the right talent."},
  {type: 'quote', mediaType: 'text', url: 'https://hbr.org/2014/09/whats-your-language-strategy', description: "Language is a vital link of our talent management strategy.  Even if your company decides not to adopt a lingua franca, you can’t neglect language.  In fact, it should touch every talent decision you make as a global leader."}
].map(function(item) {
  item.published = true;
  item.dateCreated = item.dateModified = item.datePublished = currentDate;
  
  return item;
});

// fetch all existing content (if any)
var existingItems = db.resourceLinks.find({}).toArray();

var count = 0;
for (var i in resources) {
  var item = resources[i];
  
  // ensure that item doesn't already exist
  var exists = false;
  if (existingItems.length > 0) {
    for (var j in existingItems) {
      var existingItem = existingItems[j];
      if (item.type == existingItem.type && item.url == existingItem.url) {
        exists = true;
      }
    }
  }

  if (!exists) {
    db.resourceLinks.save(item);
    count++;
  }
}

print(count + " items added into resourceLinks collection.");
