<?php

namespace aportela\MusicBrainzWrapper;

/**
 * https://musicbrainz.org/relationships/artist-url
 */
enum ArtistURLRelationshipType: string
{
    case OFFICIAL_HOMEPAGE = "fe33d22f-c3b0-4d68-bd53-a856badf2b15";
    case DISCOGRAPHY = "d0c5cf3a-8afb-4d24-ad47-00f43dc509fe";
    case FANPAGE = "f484f897-81cc-406e-96f9-cd799a04ee24";
    case BIOGRAPHY = "78f75830-94e1-4138-8f8a-643e3bb21ce5";
    case LYRICS = "e4d73442-3762-45a8-905c-401da65544ed";
    case SOCIAL_MYSPACE = "bac47923-ecde-4b59-822e-d08f0cd10156";
    case SOCIAL_SOUNDCLOUD = "89e4a949-0976-440d-bda1-5f772c1e5710";
    case SOCIAL_YOUTUBE = "6a540e5b-58c6-4192-b6ba-dbc71ec8fcf0";
    case ONLINE_COMMUNITY = "35b3a50f-bf0e-4309-a3b4-58eeed8cee6a";
    case BLOG = "eb535226-f8ca-499d-9b18-6a144df4ae6f";
    case DATABASE_ALLMUSIC = "6b3e3c85-0002-4f34-aca6-80ace0d7e846";
    case DATABASE_DISCOGS = "04a5b104-a4c2-4bac-99a1-7b837c37d9e4";
    case DATABASE_IMDB = "94c8b0cc-4477-4106-932c-da60e63de61c";
    case DATABASE_LASTFM = "08db8098-c0df-4b78-82c3-c8697b4bba7f";
    case DATABASE_WIKIDATA = "689870a4-a1e4-4912-b17f-7b2664215698";
    case DATABASE_WIKIPEDIA = "29651736-fa6d-48e4-aadc-a557c6add1cb";
}
