# Configuration de Solr

Pour fonctionner avec NewspaperReader, Solr doit avoir une configuration particulière

## db-data-config.xml

```xml
<dataConfig>
<dataSource type="JdbcDataSource"
    driver="com.mysql.jdbc.Driver"
    url="jdbc:mysql://<host>/<database>"
    user="<user>"
    password="<pass>"
    batchSize="5" />

<document name="documents">
<entity name="document" query="SELECT omeka_newspaper_reader_documents.id,
       omeka_newspaper_reader_documents.id_item,
       omeka_search_texts.title,
       collection_id as id_collection,
       omeka_search_texts.record_type,
       concat( omeka_element_texts.text , 'T00:00:00Z' ) AS date
FROM omeka_newspaper_reader_documents,
     omeka_search_texts,
     omeka_element_texts,
     omeka_items
WHERE omeka_newspaper_reader_documents.id_item = omeka_search_texts.record_id
    AND omeka_items.public = 1
    AND omeka_search_texts.record_type = 'Item'
    AND omeka_element_texts.record_id = omeka_newspaper_reader_documents.id_item
    AND omeka_element_texts.record_type = 'Item'
    AND omeka_element_texts.element_id = 40
    AND omeka_items.id = omeka_newspaper_reader_documents.id_item

">

        <field column="omeka_newspaper_reader_documents.id" name="id" />
        <field column="omeka_newspaper_reader_documents.id_item" name="id_item" />
        <field column="title" name="title" />
        <field column="record_type" name="resulttype" />
        <field column="date" name="date" />

        <entity name="coll" query="
            SELECT
                omeka_items.collection_id,
                text
            FROM
                omeka_items,
                omeka_element_texts
            WHERE
                ${document.id_item} = omeka_items.id
            AND omeka_element_texts.record_id = omeka_items.collection_id
            AND omeka_element_texts.record_type = 'Collection'
        ">

                <field column="collection_id" name="collection" />
                <field column="text" name="collection_nom" />

                <entity name="view" query="select documents.id, views.id, number, text from omeka_newspaper_reader_documents as documents , omeka_newspaper_reader_views as views where ${document.id}=documents.id and documents.id=views.id_document">

                        <field column="omeka_newspaper_reader_documents.id" name="id_doc" />
                        <field column="omeka_newspaper_reader_views.id" name="id.view" />
                        <field column="text" name="*_contenu" />
                        <field column="number" name="*_page" />

                        <entity name="image" query="select filename from omeka_files where item_id=${document.id_item}">

                                <field column="filename" name="filename" />

                        </entity>

                </entity>
        </entity>
</entity>

</document>
</dataConfig>
```

## schema.xml

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<schema name="SolrSearch" version="1.5">

 <fields>

    <field name="id" type="string" indexed="true" stored="true" required="true" />
    <field name="title" type="textgen" indexed="true" stored="true" multiValued="true" />
    <field name="tag" type="string" indexed="true" stored="true" multiValued="true" />
    <field name="collection" type="string" indexed="true" stored="true" />
    <field name="collection_nom" type="string" indexed="true" stored="true" />
    <field name="featured" type="boolean" indexed="true" stored="true" />
    <field name="itemtype" type="string" indexed="true" stored="true" />
    <field name="resulttype" type="string" indexed="true" stored="true" />
    <field name="model" type="string" indexed="false" stored="true" />
    <field name="modelid" type="string" indexed="false" stored="true" />
    <field name="text" type="textgen" indexed="true" stored="false" multiValued="true"/>
    <field name="_version_" type="long" indexed="true" stored="true"/>

    <dynamicField name="*_t" type="textgen" indexed="true" stored="true" multiValued="true" />
    <dynamicField name="*_s" type="string" indexed="true" stored="true" multiValued="true" />

    <field name="public" type="string" indexed="true" stored="true" />
    <field name="id_item" type="long" indexed="true" stored="true" />
    <field name="id_collection" type="long" indexed="true" stored="true" />
    <field name="id_doc" type="long" indexed="true" stored="true" />
    <field name="id_view" type="long" indexed="true" stored="true" />
    <field name="filename" type="string" indexed="false" stored="true" />
    <field name="date" type="date" indexed="true" stored="true" />
    <dynamicField name="*_contenu" type="textgen" indexed="true" stored="false" multiValued="true" />
    <dynamicField name="*_page" type="string" indexed="true" stored="false" multiValued="true" />

  </fields>


  <uniqueKey>id</uniqueKey>

  <solrQueryParser defaultOperator="AND" />

  <copyField source="*_t" dest="text" />
  <copyField source="tag" dest="text" />
  <copyField source="collection" dest="text" />
  <copyField source="itemtype" dest="text" />
  <copyField source="resulttype" dest="text" />
  <copyField source="title" dest="text" />
  <copyField source="*_contenu" dest="text" />
  <types>

    <fieldType name="string" class="solr.StrField" sortMissingLast="true" />

    <fieldType name="boolean" class="solr.BoolField" sortMissingLast="true"/>

    <fieldType name="int" class="solr.TrieIntField" precisionStep="0" positionIncrementGap="0"/>
    <fieldType name="float" class="solr.TrieFloatField" precisionStep="0" positionIncrementGap="0"/>
    <fieldType name="long" class="solr.TrieLongField" precisionStep="0" positionIncrementGap="0"/>
    <fieldType name="double" class="solr.TrieDoubleField" precisionStep="0" positionIncrementGap="0"/>

    <fieldType name="tint" class="solr.TrieIntField" precisionStep="8" positionIncrementGap="0"/>
    <fieldType name="tfloat" class="solr.TrieFloatField" precisionStep="8" positionIncrementGap="0"/>
    <fieldType name="tlong" class="solr.TrieLongField" precisionStep="8" positionIncrementGap="0"/>
    <fieldType name="tdouble" class="solr.TrieDoubleField" precisionStep="8" positionIncrementGap="0"/>

    <fieldType name="date" class="solr.TrieDateField" precisionStep="0" positionIncrementGap="0"/>

    <fieldType name="tdate" class="solr.TrieDateField" precisionStep="6" positionIncrementGap="0"/>


    <fieldtype name="binary" class="solr.BinaryField"/>

    <fieldType name="pint" class="solr.IntField"/>
    <fieldType name="plong" class="solr.LongField"/>
    <fieldType name="pfloat" class="solr.FloatField"/>
    <fieldType name="pdouble" class="solr.DoubleField"/>
    <fieldType name="pdate" class="solr.DateField" sortMissingLast="true"/>

    <fieldType name="random" class="solr.RandomSortField" indexed="true" />



    <fieldType name="text_ws" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_general" class="solr.TextField" positionIncrementGap="100">
      <analyzer type="index">
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="stopwords.txt" enablePositionIncrements="true" />
        <filter class="solr.LowerCaseFilterFactory"/>
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="stopwords.txt" enablePositionIncrements="true" />
        <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="true"/>
        <filter class="solr.LowerCaseFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="textgen" class="solr.TextField" positionIncrementGap="100">
      <analyzer type="index">
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.ASCIIFoldingFilterFactory"/>
        <filter class="solr.EdgeNGramFilterFactory" minGramSize="1" maxGramSize="50" side="front"/>
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.ASCIIFoldingFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_en" class="solr.TextField" positionIncrementGap="100">
      <analyzer type="index">
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="lang/stopwords_en.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.LowerCaseFilterFactory"/>
    <filter class="solr.EnglishPossessiveFilterFactory"/>
        <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
        <filter class="solr.PorterStemFilterFactory"/>
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="true"/>
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="lang/stopwords_en.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.LowerCaseFilterFactory"/>
    <filter class="solr.EnglishPossessiveFilterFactory"/>
        <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
        <filter class="solr.PorterStemFilterFactory"/>
      </analyzer>
    </fieldType>


    <fieldType name="text_en_splitting" class="solr.TextField" positionIncrementGap="100" autoGeneratePhraseQueries="true">
      <analyzer type="index">
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="lang/stopwords_en.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1" catenateWords="1" catenateNumbers="1" catenateAll="0" splitOnCaseChange="1"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
        <filter class="solr.PorterStemFilterFactory"/>
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="true"/>
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="lang/stopwords_en.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1" catenateWords="0" catenateNumbers="0" catenateAll="0" splitOnCaseChange="1"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
        <filter class="solr.PorterStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_en_splitting_tight" class="solr.TextField" positionIncrementGap="100" autoGeneratePhraseQueries="true">
      <analyzer>
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="false"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_en.txt"/>
        <filter class="solr.WordDelimiterFilterFactory" generateWordParts="0" generateNumberParts="0" catenateWords="1" catenateNumbers="1" catenateAll="0"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
        <filter class="solr.EnglishMinimalStemFilterFactory"/>

        <filter class="solr.RemoveDuplicatesTokenFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_general_rev" class="solr.TextField" positionIncrementGap="100">
      <analyzer type="index">
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="stopwords.txt" enablePositionIncrements="true" />
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.ReversedWildcardFilterFactory" withOriginal="true"
           maxPosAsterisk="3" maxPosQuestion="2" maxFractionAsterisk="0.33"/>
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="true"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="stopwords.txt" enablePositionIncrements="true" />
        <filter class="solr.LowerCaseFilterFactory"/>
      </analyzer>
    </fieldType>



    <fieldType name="alphaOnlySort" class="solr.TextField" sortMissingLast="true" omitNorms="true">
      <analyzer>
        <tokenizer class="solr.KeywordTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory" />
        <filter class="solr.TrimFilterFactory" />

        <filter class="solr.PatternReplaceFilterFactory"
                pattern="([^a-z])" replacement="" replace="all"
        />
      </analyzer>
    </fieldType>

    <fieldtype name="phonetic" stored="false" indexed="true" class="solr.TextField" >
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.DoubleMetaphoneFilterFactory" inject="false"/>
      </analyzer>
    </fieldtype>

    <fieldtype name="payloads" stored="false" indexed="true" class="solr.TextField" >
      <analyzer>
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>

        <filter class="solr.DelimitedPayloadTokenFilterFactory" encoder="float"/>
      </analyzer>
    </fieldtype>

    <fieldType name="lowercase" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.KeywordTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory" />
      </analyzer>
    </fieldType>

      <fieldType name="text_en_synonymsLcsh" class="solr.TextField" positionIncrementGap="100">
          <analyzer type="index">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
          <analyzer type="query">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.SynonymFilterFactory" synonyms="synonymsLcsh.txt" ignoreCase="true" expand="true"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>


              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
      </fieldType>


      <fieldType name="text_en_synonymsStateLcsh" class="solr.TextField" positionIncrementGap="100">
          <analyzer type="index">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
          <analyzer type="query">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.SynonymFilterFactory" synonyms="synonymsState.txt" ignoreCase="true" expand="true"/>
              <filter class="solr.SynonymFilterFactory" synonyms="synonymsLcsh.txt" ignoreCase="true" expand="true"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>


              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
      </fieldType>


      <fieldType name="text_en_synonymsLcshIso" class="solr.TextField" positionIncrementGap="100">
          <analyzer type="index">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
          <analyzer type="query">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.SynonymFilterFactory" synonyms="synonymsLcsh.txt" ignoreCase="true" expand="true"/>
              <filter class="solr.SynonymFilterFactory" synonyms="synonymsIso.txt" ignoreCase="true" expand="true"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>


              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
      </fieldType>


      <fieldType name="text_en_synonymsState" class="solr.TextField" positionIncrementGap="100">
          <analyzer type="index">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
          <analyzer type="query">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.SynonymFilterFactory" synonyms="synonymsState.txt" ignoreCase="true" expand="true"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>


              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
      </fieldType>
      <fieldType name="text_en_synonymsIso" class="solr.TextField" positionIncrementGap="100">
          <analyzer type="index">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>
              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
          <analyzer type="query">
              <tokenizer class="solr.StandardTokenizerFactory"/>

              <filter class="solr.SynonymFilterFactory" synonyms="synonymsIso.txt" ignoreCase="true" expand="true"/>

              <filter class="solr.StopFilterFactory"
              ignoreCase="true"
              words="lang/stopwords_en.txt"
              enablePositionIncrements="true"
              />
              <filter class="solr.LowerCaseFilterFactory"/>
              <filter class="solr.EnglishPossessiveFilterFactory"/>
              <filter class="solr.KeywordMarkerFilterFactory" protected="protwords.txt"/>


              <filter class="solr.PorterStemFilterFactory"/>
          </analyzer>
      </fieldType>



    <fieldType name="descendent_path" class="solr.TextField">
      <analyzer type="index">
    <tokenizer class="solr.PathHierarchyTokenizerFactory" delimiter="/" />
      </analyzer>
      <analyzer type="query">
    <tokenizer class="solr.KeywordTokenizerFactory" />
      </analyzer>
    </fieldType>
    <fieldType name="ancestor_path" class="solr.TextField">
      <analyzer type="index">
    <tokenizer class="solr.KeywordTokenizerFactory" />
      </analyzer>
      <analyzer type="query">
    <tokenizer class="solr.PathHierarchyTokenizerFactory" delimiter="/" />
      </analyzer>
    </fieldType>

    <fieldtype name="ignored" stored="false" indexed="false" multiValued="true" class="solr.StrField" />

    <fieldType name="point" class="solr.PointType" dimension="2" subFieldSuffix="_d"/>

    <fieldType name="location" class="solr.LatLonType" subFieldSuffix="_coordinate"/>

    <fieldType name="location_rpt" class="solr.SpatialRecursivePrefixTreeFieldType"
        geo="true" distErrPct="0.025" maxDistErr="0.000009" units="degrees" />

    <fieldType name="currency" class="solr.CurrencyField" precisionStep="8" defaultCurrency="USD" currencyConfig="currency.xml" />




    <fieldType name="text_ar" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_ar.txt" enablePositionIncrements="true"/>
        <filter class="solr.ArabicNormalizationFilterFactory"/>
        <filter class="solr.ArabicStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_bg" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_bg.txt" enablePositionIncrements="true"/>
        <filter class="solr.BulgarianStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_ca" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.ElisionFilterFactory" ignoreCase="true" articles="lang/contractions_ca.txt"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_ca.txt" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Catalan"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_cjk" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.CJKWidthFilterFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.CJKBigramFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_cz" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_cz.txt" enablePositionIncrements="true"/>
        <filter class="solr.CzechStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_da" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_da.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Danish"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_de" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_de.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.GermanNormalizationFilterFactory"/>
        <filter class="solr.GermanLightStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_el" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.GreekLowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="false" words="lang/stopwords_el.txt" enablePositionIncrements="true"/>
        <filter class="solr.GreekStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_es" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_es.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SpanishLightStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_eu" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_eu.txt" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Basque"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_fa" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <charFilter class="solr.PersianCharFilterFactory"/>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.ArabicNormalizationFilterFactory"/>
        <filter class="solr.PersianNormalizationFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_fa.txt" enablePositionIncrements="true"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_fi" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_fi.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Finnish"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_fr" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.ElisionFilterFactory" ignoreCase="true" articles="lang/contractions_fr.txt"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_fr.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.FrenchLightStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_ga" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.ElisionFilterFactory" ignoreCase="true" articles="lang/contractions_ga.txt"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/hyphenations_ga.txt" enablePositionIncrements="false"/>
        <filter class="solr.IrishLowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_ga.txt" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Irish"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_gl" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_gl.txt" enablePositionIncrements="true"/>
        <filter class="solr.GalicianStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_hi" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.IndicNormalizationFilterFactory"/>
        <filter class="solr.HindiNormalizationFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_hi.txt" enablePositionIncrements="true"/>
        <filter class="solr.HindiStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_hu" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_hu.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Hungarian"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_hy" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_hy.txt" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Armenian"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_id" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_id.txt" enablePositionIncrements="true"/>
        <filter class="solr.IndonesianStemFilterFactory" stemDerivational="true"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_it" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.ElisionFilterFactory" ignoreCase="true" articles="lang/contractions_it.txt"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_it.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.ItalianLightStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_ja" class="solr.TextField" positionIncrementGap="100" autoGeneratePhraseQueries="false">
      <analyzer>

        <tokenizer class="solr.JapaneseTokenizerFactory" mode="search"/>
        <filter class="solr.JapaneseBaseFormFilterFactory"/>
        <filter class="solr.JapanesePartOfSpeechStopFilterFactory" tags="lang/stoptags_ja.txt" enablePositionIncrements="true"/>
        <filter class="solr.CJKWidthFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_ja.txt" enablePositionIncrements="true" />
        <filter class="solr.JapaneseKatakanaStemFilterFactory" minimumLength="4"/>
        <filter class="solr.LowerCaseFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_lv" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_lv.txt" enablePositionIncrements="true"/>
        <filter class="solr.LatvianStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_nl" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_nl.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.StemmerOverrideFilterFactory" dictionary="lang/stemdict_nl.txt" ignoreCase="false"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Dutch"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_no" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_no.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Norwegian"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_pt" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_pt.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.PortugueseLightStemFilterFactory"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_ro" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_ro.txt" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Romanian"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_ru" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_ru.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Russian"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_sv" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_sv.txt" format="snowball" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Swedish"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_th" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.ThaiWordFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="true" words="lang/stopwords_th.txt" enablePositionIncrements="true"/>
      </analyzer>
    </fieldType>

    <fieldType name="text_tr" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.StandardTokenizerFactory"/>
        <filter class="solr.TurkishLowerCaseFilterFactory"/>
        <filter class="solr.StopFilterFactory" ignoreCase="false" words="lang/stopwords_tr.txt" enablePositionIncrements="true"/>
        <filter class="solr.SnowballPorterFilterFactory" language="Turkish"/>
      </analyzer>
    </fieldType>

 </types>


</schema>

```
