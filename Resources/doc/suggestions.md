# Suggestions

The SuggestComponent in Solr provides users with automatic suggestions for query terms (see [solr docs](https://lucene.apache.org/solr/guide/6_6/suggester.html)). **DoctrineSolrBundle** supports integration with SuggestComponent with **"\Mdiyakov\DoctrineSolrBundle\Suggester\ClassSuggester"** and **"\Mdiyakov\DoctrineSolrBundle\Suggester\SchemaSuggester"** implementations.

### Content

[Setting up](#setting-up)
[ClassSuggester](#classSuggester)
[SchemaSuggester](#schemaSuggester)


### Setting up

To start using the suggester you have to specify in schema config section which entity field can be used as source of suggestions:
```
schemes:
    page:
        ...
        fields:
           ...
            - {  entity_field_name: 'category', document_field_name: 'category', suggester: 'category' }
            - {  entity_field_name: 'title', document_field_name: 'title' , priority: 100 , suggester: 'title' }
            - {  entity_field_name: 'text', document_field_name: 'page_body' }
            ...
```
Here we add to both "title" and "category" entity fields "suggester" attribute. The value of "suggester" attribute is solr dictionary name configured in solconfig.xml ([solr doc](https://lucene.apache.org/solr/guide/6_6/suggester.html#Suggester-AddingtheSuggestSearchComponent)). For example the config of Suggest Search Component in solrconfig.xml can look like:
```
 <searchComponent name="suggest" class="solr.SuggestComponent">
         <lst name="suggester">
             <str name="name">title</str>
             ...             
         </lst>
         <lst name="suggester">
             <str name="name">category</str>
             ...
         </lst>             
     </searchComponent>
```
so the value of "suggester"" attribute can be "title" or "category". After you specify "suggester" attribute of entity field you can use this field for getting the suggestions.

> Pay attention the query time building of lookup data is not supported by DoctrineSolrBundle. So you have to use corresponding settings like "buildOnCommit" or "buildOnOptimize" or "buildOnStartup"  inside solrconfig.xml:
```
<searchComponent name="suggest" class="solr.SuggestComponent">
  <lst name="suggester">
    <str name="name">mySuggester</str>
    ...
    <str name="buildOnStartup">true</str>
  </lst>
</searchComponent>
```
 
 
### ClassSuggester 
 
If you want to get suggestions within single entity class you have to use **ClassSuggester**.
> The important thing here you have to provide additional config for search component in solrconfig.xml to use ClassSuggester. You have to specify "contextField" (see ["Context Filtering"](https://lucene.apache.org/solr/guide/6_6/suggester.html#Suggester-ContextFiltering)) The value of "contextField" must be a value of "document_field_name" attribute of discriminator config field. For example you have schema config section like:
```
schemes:
    page:
        ...
        config_entity_fields:
            - {  config_field_name: 'type', document_field_name: 'discriminator_field', discriminator: true  }
           ...
```
then in solrconfig.yml you have to specify "contextField" for search component like:
```
 <searchComponent name="suggest" class="solr.SuggestComponent">
         <lst name="suggester">
            <str name="name">title</str>
            <str name="contextField">discriminator_field</str>
            ...
         </lst>
 </searchComponent>         
```
Also please pay attention "Context Filtering" have limited set of supported lookup implementations (["lookupImpl"](https://lucene.apache.org/solr/guide/6_6/suggester.html#Suggester-LookupImplementations)) and  dictionary implementations (["dictionaryImpl"](https://lucene.apache.org/solr/guide/6_6/suggester.html#Suggester-DictionaryImplementations)). See solr docs for more details. 


How to use:
```
$pageSuggester = $this->get('ds.suggester')->getClassSuggester(Page::class);
$suggestions = $pageSuggester->suggestByFields($searchTerm, ['title', ...]);

$titleFieldSuggestions = $suggestions->getResultsByField('title');

$terms = $titleFieldSuggestions->getSuggestions();
foreach ($terms as $term) {
    $term->getTerm();
    $term->getWeight();
    $term->getPayload();
}
```  



### SchemaSuggester
If you want to get suggestions across all entity classes using shared schema config you have to use **SchemaSuggester**.
```
$schemaSuggester = $this->get('ds.suggester')->getSchemaSuggester('page');
$results = $schemaSuggester->suggestByFields($searchTerm, ['title',...]);
```
        
In this case all entity classes will be used not depending on "ContextFiltering" settings in solrconfig.xml


## Pages
* [Getting started with DoctrineSolrBundle] (getting_started.md)
* [ Regular, fuzzy, wildcard, range and negative search](fuzzy_wildcard_range_negative_search.md) 
* [ Custom finder class ](custom_finder_class.md)
* [ Filters ](filters.md)
* [Schema search over multiple entities classes](schema_search.md)
* [Query building](query_building.md)
* [Console command to index entities](console.md)