<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\DublinCoreBundle\Entity\Element;

class TermFixtures extends Fixture implements FixtureGroupInterface
{
    public const URI_PFX = 'http://purl.org/dc/terms/';

    public const NAME_PFX = 'dcterms_';

    private function getData() {
        return [
            [
                'name' => 'dcterms_abstract',
                'label' => 'Abstract',
                'uri' => 'http://purl.org/dc/terms/abstract',
                'description' => 'A summary of the resource.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_accessRights',
                'label' => 'Access Rights',
                'uri' => 'http://purl.org/dc/terms/accessRights',
                'description' => 'Information about who access the resource or an indication of its security status.',
                'comment' => 'Access Rights may include information regarding access or restrictions based on privacy, security, or other policies.',
            ],
            [
                'name' => 'dcterms_accrualMethod',
                'label' => 'Accrual Method',
                'uri' => 'http://purl.org/dc/terms/accrualMethod',
                'description' => 'The method by which items are added to a collection.',
                'comment' => 'Recommended practice is to use a value from the Collection Description Accrual Method Vocabulary [[DCMI-ACCRUALMETHOD](https://dublincore.org/groups/collections/accrual-method/)].',
            ],
            [
                'name' => 'dcterms_accrualPeriodicity',
                'label' => 'Accrual Periodicity',
                'uri' => 'http://purl.org/dc/terms/accrualPeriodicity',
                'description' => 'The frequency with which items are added to a collection.',
                'comment' => 'Recommended practice is to use a value from the Collection Description Frequency Vocabulary [[DCMI-COLLFREQ](https://dublincore.org/groups/collections/frequency/)].',
            ],
            [
                'name' => 'dcterms_accrualPolicy',
                'label' => 'Accrual Policy',
                'uri' => 'http://purl.org/dc/terms/accrualPolicy',
                'description' => 'The policy governing the addition of items to a collection.',
                'comment' => 'Recommended practice is to use a value from the Collection Description Accrual Policy Vocabulary [[DCMI-ACCRUALPOLICY](https://dublincore.org/groups/collections/accrual-policy/)].',
            ],
            [
                'name' => 'dcterms_alternative',
                'label' => 'Alternative Title',
                'uri' => 'http://purl.org/dc/terms/alternative',
                'description' => 'An alternative name for the resource.',
                'comment' => 'The distinction between titles and alternative titles is application-specific.',
            ],
            [
                'name' => 'dcterms_audience',
                'label' => 'Audience',
                'uri' => 'http://purl.org/dc/terms/audience',
                'description' => 'A class of agents for whom the resource is intended or useful.',
                'comment' => 'Recommended practice is to use this property with non-literal values from a vocabulary of audience types.',
            ],
            [
                'name' => 'dcterms_available',
                'label' => 'Date Available',
                'uri' => 'http://purl.org/dc/terms/available',
                'description' => 'Date that the resource became or will become available.',
                'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            ],
            [
                'name' => 'dcterms_bibliographicCitation',
                'label' => 'Bibliographic Citation',
                'uri' => 'http://purl.org/dc/terms/bibliographicCitation',
                'description' => 'A bibliographic reference for the resource.',
                'comment' => 'Recommended practice is to include sufficient bibliographic detail to identify the resource as unambiguously as possible.',
            ],
            [
                'name' => 'dcterms_conformsTo',
                'label' => 'Conforms To',
                'uri' => 'http://purl.org/dc/terms/conformsTo',
                'description' => 'An established standard to which the described resource conforms.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_contributor',
                'label' => 'Contributor',
                'uri' => 'http://purl.org/dc/terms/contributor',
                'description' => 'An entity responsible for making contributions to the resource.',
                'comment' => 'The guidelines for using names of persons or organizations as creators apply to contributors.',
            ],
            [
                'name' => 'dcterms_coverage',
                'label' => 'Coverage',
                'uri' => 'http://purl.org/dc/terms/coverage',
                'description' => 'The spatial or temporal topic of the resource, spatial applicability of the resource, or jurisdiction under which the resource is relevant.',
                'comment' => 'Spatial topic and spatial applicability may be a named place or a location specified by its geographic coordinates. Temporal topic may be a named period, date, or date range. A jurisdiction may be a named administrative entity or a geographic place to which the resource applies. Recommended practice is to use a controlled vocabulary such as the Getty Thesaurus of Geographic Names [[TGN](https://www.getty.edu/research/tools/vocabulary/tgn/index.html)]. Where appropriate, named places or time periods may be used in preference to numeric identifiers such as sets of coordinates or date ranges.  Because coverage is so broadly defined, it is preferable to use the more specific subproperties Temporal Coverage and Spatial Coverage.',
            ],
            [
                'name' => 'dcterms_created',
                'label' => 'Date Created',
                'uri' => 'http://purl.org/dc/terms/created',
                'description' => 'Date of creation of the resource.',
                'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            ],
            [
                'name' => 'dcterms_creator',
                'label' => 'Creator',
                'uri' => 'http://purl.org/dc/terms/creator',
                'description' => 'An entity responsible for making the resource.',
                'comment' => 'Recommended practice is to identify the creator with a URI.  If this is not possible or feasible, a literal value that identifies the creator may be provided.',
            ],
            [
                'name' => 'dcterms_date',
                'label' => 'Date',
                'uri' => 'http://purl.org/dc/terms/date',
                'description' => 'A point or period of time associated with an event in the lifecycle of the resource.',
                'comment' => "Date may be used to express temporal information at any level of granularity.  Recommended practice is to express the date, date/time, or period of time according to ISO 8601-1 [[ISO 8601-1](https://www.iso.org/iso-8601-date-and-time-format.html)] or a published profile of the ISO standard, such as the W3C Note on Date and Time Formats [[W3CDTF](https://www.w3.org/TR/NOTE-datetime)] or the Extended Date/Time Format Specification [[EDTF](http://www.loc.gov/standards/datetime/)].  If the full date is unknown, month and year (YYYY-MM) or just year (YYYY) may be used. Date ranges may be specified using ISO 8601 period of time specification in which start and end dates are separated by a '/' (slash) character.  Either the start or end date may be missing.",
            ],
            [
                'name' => 'dcterms_dateAccepted',
                'label' => 'Date Accepted',
                'uri' => 'http://purl.org/dc/terms/dateAccepted',
                'description' => 'Date of acceptance of the resource.',
                'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.  Examples of resources to which a date of acceptance may be relevant are a thesis (accepted by a university department) or an article (accepted by a journal).',
            ],
            [
                'name' => 'dcterms_dateCopyrighted',
                'label' => 'Date Copyrighted',
                'uri' => 'http://purl.org/dc/terms/dateCopyrighted',
                'description' => 'Date of copyright of the resource.',
                'comment' => 'Typically a year.  Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            ],
            [
                'name' => 'dcterms_dateSubmitted',
                'label' => 'Date Submitted',
                'uri' => 'http://purl.org/dc/terms/dateSubmitted',
                'description' => 'Date of submission of the resource.',
                'comment' => "Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.  Examples of resources to which a 'Date Submitted' may be relevant include a thesis (submitted to a university department) or an article (submitted to a journal).",
            ],
            [
                'name' => 'dcterms_description',
                'label' => 'Description',
                'uri' => 'http://purl.org/dc/terms/description',
                'description' => 'An account of the resource.',
                'comment' => 'Description may include but is not limited to',
            ],
            [
                'name' => 'dcterms_educationLevel',
                'label' => 'Audience Education Level',
                'uri' => 'http://purl.org/dc/terms/educationLevel',
                'description' => 'A class of agents, defined in terms of progression through an educational or training context, for which the described resource is intended.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_extent',
                'label' => 'Extent',
                'uri' => 'http://purl.org/dc/terms/extent',
                'description' => 'The size or duration of the resource.',
                'comment' => 'Recommended practice is to specify the file size in megabytes and duration in ISO 8601 format.',
            ],
            [
                'name' => 'dcterms_format',
                'label' => 'Format',
                'uri' => 'http://purl.org/dc/terms/format',
                'description' => 'The file format, physical medium, or dimensions of the resource.',
                'comment' => 'Recommended practice is to use a controlled vocabulary where available. For example, for file formats one could use the list of Internet Media Types [[MIME](https://www.iana.org/assignments/media-types/media-types.xhtml)].  Examples of dimensions include size and duration.',
            ],
            [
                'name' => 'dcterms_hasFormat',
                'label' => 'Has Format',
                'uri' => 'http://purl.org/dc/terms/hasFormat',
                'description' => 'A related resource that is substantially the same as the pre-existing described resource, but in another format.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Format Of.',
            ],
            [
                'name' => 'dcterms_hasPart',
                'label' => 'Has Part',
                'uri' => 'http://purl.org/dc/terms/hasPart',
                'description' => 'A related resource that is included either physically or logically in the described resource.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Part Of.',
            ],
            [
                'name' => 'dcterms_hasVersion',
                'label' => 'Has Version',
                'uri' => 'http://purl.org/dc/terms/hasVersion',
                'description' => 'A related resource that is a version, edition, or adaptation of the described resource.',
                'comment' => 'Changes in version imply substantive changes in content rather than differences in format. This property is intended to be used with non-literal values. This property is an inverse property of Is Version Of.',
            ],
            [
                'name' => 'dcterms_identifier',
                'label' => 'Identifier',
                'uri' => 'http://purl.org/dc/terms/identifier',
                'description' => 'An unambiguous reference to the resource within a given context.',
                'comment' => 'Recommended practice is to identify the resource by means of a string conforming to an identification system. Examples include International Standard Book Number (ISBN), Digital Object Identifier (DOI), and Uniform Resource Name (URN).  Persistent identifiers should be provided as HTTP URIs.',
            ],
            [
                'name' => 'dcterms_instructionalMethod',
                'label' => 'Instructional Method',
                'uri' => 'http://purl.org/dc/terms/instructionalMethod',
                'description' => 'A process, used to engender knowledge, attitudes and skills, that the described resource is designed to support.',
                'comment' => 'Instructional Method typically includes ways of presenting instructional materials or conducting instructional activities, patterns of learner-to-learner and learner-to-instructor interactions, and mechanisms by which group and individual levels of learning are measured.  Instructional methods include all aspects of the instruction and learning processes from planning and implementation through evaluation and feedback.',
            ],
            [
                'name' => 'dcterms_isFormatOf',
                'label' => 'Is Format Of',
                'uri' => 'http://purl.org/dc/terms/isFormatOf',
                'description' => 'A pre-existing related resource that is substantially the same as the described resource, but in another format.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Has Format.',
            ],
            [
                'name' => 'dcterms_isPartOf',
                'label' => 'Is Part Of',
                'uri' => 'http://purl.org/dc/terms/isPartOf',
                'description' => 'A related resource in which the described resource is physically or logically included.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Has Part.',
            ],
            [
                'name' => 'dcterms_isReferencedBy',
                'label' => 'Is Referenced By',
                'uri' => 'http://purl.org/dc/terms/isReferencedBy',
                'description' => 'A related resource that references, cites, or otherwise points to the described resource.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of References.',
            ],
            [
                'name' => 'dcterms_isReplacedBy',
                'label' => 'Is Replaced By',
                'uri' => 'http://purl.org/dc/terms/isReplacedBy',
                'description' => 'A related resource that supplants, displaces, or supersedes the described resource.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Replaces.',
            ],
            [
                'name' => 'dcterms_isRequiredBy',
                'label' => 'Is Required By',
                'uri' => 'http://purl.org/dc/terms/isRequiredBy',
                'description' => 'A related resource that requires the described resource to support its function, delivery, or coherence.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Requires.',
            ],
            [
                'name' => 'dcterms_isVersionOf',
                'label' => 'Is Version Of',
                'uri' => 'http://purl.org/dc/terms/isVersionOf',
                'description' => 'A related resource of which the described resource is a version, edition, or adaptation.',
                'comment' => 'Changes in version imply substantive changes in content rather than differences in format. This property is intended to be used with non-literal values. This property is an inverse property of Has Version.',
            ],
            [
                'name' => 'dcterms_issued',
                'label' => 'Date Issued',
                'uri' => 'http://purl.org/dc/terms/issued',
                'description' => 'Date of formal issuance of the resource.',
                'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            ],
            [
                'name' => 'dcterms_language',
                'label' => 'Language',
                'uri' => 'http://purl.org/dc/terms/language',
                'description' => 'A language of the resource.',
                'comment' => 'Recommended practice is to use either a non-literal value representing a language from a controlled vocabulary such as ISO 639-2 or ISO 639-3, or a literal value consisting of an IETF Best Current Practice 47 [[IETF-BCP47](https://tools.ietf.org/html/bcp47)] language tag.',
            ],
            [
                'name' => 'dcterms_license',
                'label' => 'License',
                'uri' => 'http://purl.org/dc/terms/license',
                'description' => 'A legal document giving official permission to do something with the resource.',
                'comment' => 'Recommended practice is to identify the license document with a URI. If this is not possible or feasible, a literal value that identifies the license may be provided.',
            ],
            [
                'name' => 'dcterms_mediator',
                'label' => '',
                'uri' => 'http://purl.org/dc/terms/mediator',
                'description' => 'An entity that mediates access to the resource.',
                'comment' => 'In an educational context, a mediator might be a parent, teacher, teaching assistant, or care-giver.',
            ],
            [
                'name' => 'dcterms_medium',
                'label' => 'Medium',
                'uri' => 'http://purl.org/dc/terms/medium',
                'description' => 'The material or physical carrier of the resource.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_modified',
                'label' => 'Date Modified',
                'uri' => 'http://purl.org/dc/terms/modified',
                'description' => 'Date on which the resource was changed.',
                'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            ],
            [
                'name' => 'dcterms_provenance',
                'label' => 'Provenance',
                'uri' => 'http://purl.org/dc/terms/provenance',
                'description' => 'A statement of any changes in ownership and custody of the resource since its creation that are significant for its authenticity, integrity, and interpretation.',
                'comment' => 'The statement may include a description of any changes successive custodians made to the resource.',
            ],
            [
                'name' => 'dcterms_publisher',
                'label' => 'Publisher',
                'uri' => 'http://purl.org/dc/terms/publisher',
                'description' => 'An entity responsible for making the resource available.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_references',
                'label' => 'References',
                'uri' => 'http://purl.org/dc/terms/references',
                'description' => 'A related resource that is referenced, cited, or otherwise pointed to by the described resource.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Referenced By.',
            ],
            [
                'name' => 'dcterms_relation',
                'label' => 'Relation',
                'uri' => 'http://purl.org/dc/terms/relation',
                'description' => 'A related resource.',
                'comment' => 'Recommended practice is to identify the related resource by means of a URI.  If this is not possible or feasible, a string conforming to a formal identification system may be provided.',
            ],
            [
                'name' => 'dcterms_replaces',
                'label' => 'Replaces',
                'uri' => 'http://purl.org/dc/terms/replaces',
                'description' => 'A related resource that is supplanted, displaced, or superseded by the described resource.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Replaced By.',
            ],
            [
                'name' => 'dcterms_requires',
                'label' => 'Requires',
                'uri' => 'http://purl.org/dc/terms/requires',
                'description' => 'A related resource that is required by the described resource to support its function, delivery, or coherence.',
                'comment' => 'This property is intended to be used with non-literal values. This property is an inverse property of Is Required By.',
            ],
            [
                'name' => 'dcterms_rights',
                'label' => 'Rights',
                'uri' => 'http://purl.org/dc/terms/rights',
                'description' => 'Information about rights held in and over the resource.',
                'comment' => 'Typically, rights information includes a statement about various property rights associated with the resource, including intellectual property rights.  Recommended practice is to refer to a rights statement with a URI.  If this is not possible or feasible, a literal value (name, label, or short text) may be provided.',
            ],
            [
                'name' => 'dcterms_rightsHolder',
                'label' => 'Rights Holder',
                'uri' => 'http://purl.org/dc/terms/rightsHolder',
                'description' => 'A person or organization owning or managing rights over the resource.',
                'comment' => 'Recommended practice is to refer to the rights holder with a URI. If this is not possible or feasible, a literal value that identifies the rights holder may be provided.',
            ],
            [
                'name' => 'dcterms_source',
                'label' => 'Source',
                'uri' => 'http://purl.org/dc/terms/source',
                'description' => 'A related resource from which the described resource is derived.',
                'comment' => 'This property is intended to be used with non-literal values. The described resource may be derived from the related resource in whole or in part. Best practice is to identify the related resource by means of a URI or a string conforming to a formal identification system.',
            ],
            [
                'name' => 'dcterms_spatial',
                'label' => 'Spatial Coverage',
                'uri' => 'http://purl.org/dc/terms/spatial',
                'description' => 'Spatial characteristics of the resource.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_subject',
                'label' => 'Subject',
                'uri' => 'http://purl.org/dc/terms/subject',
                'description' => 'A topic of the resource.',
                'comment' => 'Recommended practice is to refer to the subject with a URI. If this is not possible or feasible, a literal value that identifies the subject may be provided. Both should preferably refer to a subject in a controlled vocabulary.',
            ],
            [
                'name' => 'dcterms_tableOfContents',
                'label' => 'Table Of Contents',
                'uri' => 'http://purl.org/dc/terms/tableOfContents',
                'description' => 'A list of subunits of the resource.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_temporal',
                'label' => 'Temporal Coverage',
                'uri' => 'http://purl.org/dc/terms/temporal',
                'description' => 'Temporal characteristics of the resource.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_title',
                'label' => 'Title',
                'uri' => 'http://purl.org/dc/terms/title',
                'description' => 'A name given to the resource.',
                'comment' => '',
            ],
            [
                'name' => 'dcterms_type',
                'label' => 'Type',
                'uri' => 'http://purl.org/dc/terms/type',
                'description' => 'The nature or genre of the resource.',
                'comment' => 'Recommended practice is to use a controlled vocabulary such as the DCMI Type Vocabulary [[DCMI-TYPE](http://dublincore.org/documents/dcmi-type-vocabulary/)]. To describe the file format, physical medium, or dimensions of the resource, use the property Format.',
            ],
            [
                'name' => 'dcterms_valid',
                'label' => 'Date Valid',
                'uri' => 'http://purl.org/dc/terms/valid',
                'description' => 'Date (often a range) of validity of a resource.',
                'comment' => 'Recommended practice is to describe the date, date/time, or period of time as recommended for the property Date, of which this is a subproperty.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getGroups() : array {
        return [
            'nines_dcterms',
        ];
    }

    public function load(ObjectManager $manager) : void {
        foreach ($this->getData() as $data) {
            $element = new Element();
            $element->setName($data['name']);
            $element->setLabel($data['label']);
            $element->setUri($data['uri']);
            $element->setDescription($data['description']);
            $element->setComment($data['comment']);
            $manager->persist($element);
            $this->setReference($data['name'], $element);
        }

        $manager->flush();
    }
}
