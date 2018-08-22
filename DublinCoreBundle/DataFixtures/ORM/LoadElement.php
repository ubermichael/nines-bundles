<?php

namespace Nines\DublinCoreBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\DublinCoreBundle\Entity\Element;

class LoadElement extends Fixture {

    public function load(ObjectManager $manager) {
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

    private function getData() {
        return array(
            array(
                'name' => 'dc_contributor',
                'label' => 'Contributor',
                'uri' => 'http://purl.org/dc/elements/1.1/contributor',
                'description' => 'An entity responsible for making contributions to the resource.',
                'comment' => 'Examples of a Contributor include a person, an organization, or a service. Typically, the name of a Contributor should be used to indicate the entity.'
            ),
            array(
                'name' => 'dc_coverage',
                'label' => 'Coverage',
                'uri' => 'http://purl.org/dc/elements/1.1/coverage',
                'description' => 'The spatial or temporal topic of the resource, the spatial applicability of the resource, or the jurisdiction under which the resource is relevant.',
                'comment' => 'Spatial topic and spatial applicability may be a named place or a location specified by its geographic coordinates. Temporal topic may be a named period, date, or date range. A jurisdiction may be a named administrative entity or a geographic place to which the resource applies. Recommended best practice is to use a controlled vocabulary such as the Thesaurus of Geographic Names [TGN]. Where appropriate, named places or time periods can be used in preference to numeric identifiers such as sets of coordinates or date ranges.'
            ),
            array(
                'name' => 'dc_creator',
                'label' => 'Creator',
                'uri' => 'http://purl.org/dc/elements/1.1/creator',
                'description' => 'An entity primarily responsible for making the resource.',
                'comment' => 'Examples of a Creator include a person, an organization, or a service. Typically, the name of a Creator should be used to indicate the entity.'
            ),
            array(
                'name' => 'dc_date',
                'label' => 'Date',
                'uri' => 'http://purl.org/dc/elements/1.1/date',
                'description' => 'A point or period of time associated with an event in the lifecycle of the resource.',
                'comment' => 'Date may be used to express temporal information at any level of granularity. Recommended best practice is to use an encoding scheme, such as the W3CDTF profile of ISO 8601 [W3CDTF].'
            ),
            array(
                'name' => 'dc_description',
                'label' => 'Description',
                'uri' => 'http://purl.org/dc/elements/1.1/description',
                'description' => 'An account of the resource.',
                'comment' => 'Description may include but is not limited to: an abstract, a table of contents, a graphical representation, or a free-text account of the resource.'
            ),
            array(
                'name' => 'dc_format',
                'label' => 'Format',
                'uri' => 'http://purl.org/dc/elements/1.1/format',
                'description' => 'The file format, physical medium, or dimensions of the resource.',
                'comment' => 'Examples of dimensions include size and duration. Recommended best practice is to use a controlled vocabulary such as the list of Internet Media Types [MIME].'
            ),
            array(
                'name' => 'dc_identifier',
                'label' => 'Identifier',
                'uri' => 'http://purl.org/dc/elements/1.1/identifier',
                'description' => 'An unambiguous reference to the resource within a given context.',
                'comment' => 'Recommended best practice is to identify the resource by means of a string conforming to a formal identification system.'
            ),
            array(
                'name' => 'dc_language',
                'label' => 'Language',
                'uri' => 'http://purl.org/dc/elements/1.1/language',
                'description' => 'A language of the resource.',
                'comment' => 'Recommended best practice is to use a controlled vocabulary such as RFC 4646 [RFC4646].'
            ),
            array(
                'name' => 'dc_publisher',
                'label' => 'Publisher',
                'uri' => 'http://purl.org/dc/elements/1.1/publisher',
                'description' => 'An entity responsible for making the resource available.',
                'comment' => 'Examples of a Publisher include a person, an organization, or a service. Typically, the name of a Publisher should be used to indicate the entity.'
            ),
            array(
                'name' => 'dc_relation',
                'label' => 'Relation',
                'uri' => 'http://purl.org/dc/elements/1.1/relation',
                'description' => 'A related resource.',
                'comment' => 'Recommended best practice is to identify the related resource by means of a string conforming to a formal identification system.'
            ),
            array(
                'name' => 'dc_rights',
                'label' => 'Rights',
                'uri' => 'http://purl.org/dc/elements/1.1/rights',
                'description' => 'Information about rights held in and over the resource.',
                'comment' => 'Typically, rights information includes a statement about various property rights associated with the resource, including intellectual property rights.'
            ),
            array(
                'name' => 'dc_source',
                'label' => 'Source',
                'uri' => 'http://purl.org/dc/elements/1.1/source',
                'description' => 'A related resource from which the described resource is derived.',
                'comment' => 'The described resource may be derived from the related resource in whole or in part. Recommended best practice is to identify the related resource by means of a string conforming to a formal identification system.'
            ),
            array(
                'name' => 'dc_subject',
                'label' => 'Subject',
                'uri' => 'http://purl.org/dc/elements/1.1/subject',
                'description' => 'The topic of the resource.',
                'comment' => ''
            ),
            array(
                'name' => 'dc_title',
                'label' => 'Title',
                'uri' => 'http://purl.org/dc/elements/1.1/title',
                'description' => 'A name given to the resource.',
                'comment' => 'Typically, a Title will be a name by which the resource is formally known.'
            ),
            array(
                'name' => 'dc_type',
                'label' => 'Type',
                'uri' => 'http://purl.org/dc/elements/1.1/type',
                'description' => 'The nature or genre of the resource.',
                'comment' => 'Recommended best practice is to use a controlled vocabulary such as the DCMI Type Vocabulary [DCMITYPE]. To describe the file format, physical medium, or dimensions of the resource, use the Format element.'
            ),
        );
    }

}
