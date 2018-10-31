<?php

class UpworkJobsReader
{
    const RSS_FEED_URL = 'https://www.upwork.com/ab/feed/jobs/rss';

    const JOB_TYPE_ALL      = 'all';
    const JOB_TYPE_HOURLY   = 'hourly';
    const JOB_TYPE_FIXED    = 'fixed';

    const SORT_NEWEST           = 'renew_time_int';
    const SORT_RELEVANCE        = 'relevance';
    const SORT_CLIENT_SPENDING  = 'client_total_charge';
    const SORT_CLIENT_RATING    = 'client_rating';

    /*
     * Default values:
     */
    private $job_type                       = self::JOB_TYPE_ALL;
    private $experience_entry_level         = false;
    private $experience_intermediate_level  = false;
    private $experience_expert_level        = false;
    private $sort_by                        = self::SORT_NEWEST;

    public function __construct($options = [])
    {
        $this->setOptions($options);
    }

    public function fetchJobs()
    {
        $jobs = [];

        $feed = Feed::loadRss($this->getCompiledUrl());

        foreach ($feed->item as $item) {
            $jobs[] = UpworkJob::fetchFromXml($item);
        }

        return $jobs;
    }

    public function setJobType($job_type)
    {
        $this->job_type = $job_type;
    }

    public function setSortBy($sort_by)
    {
        $this->sort_by = $sort_by;
    }

    public function setExperienceEntry($status)
    {
        $this->experience_entry_level = $status;
    }

    public function setExperienceIntermediate($status)
    {
        $this->experience_intermediate_level = $status;
    }

    public function setExperienceExpert($status)
    {
        $this->experience_expert_level = $status;
    }

    public function clearExperienceFilter()
    {
        $this->experience_entry_level           = false;
        $this->experience_intermediate_level    = false;
        $this->experience_expert_level          = false;
    }

    private function setOptions($options = [])
    {
        foreach ($options as $name => $value) {
            if (property_exists(self::class, $name)) {
                $this->{$name} = $value;
            }
        }
    }

    private function getCompiledUrl()
    {
        $request_url = self::RSS_FEED_URL;

        /*
         * Sorting:
         */
        $request_url .= "?" . "sort=" . $this->sort_by . "+desc";

        /*
         * Job type:
         */
        if ($this->job_type != self::JOB_TYPE_ALL) {
            $request_url .= "&job_type=" . $this->job_type;
        }

        /*
         * Experience level:
         */
        if ($this->experience_entry_level || $this->experience_intermediate_level || $this->experience_expert_level) {

            if (!($this->experience_entry_level && $this->experience_intermediate_level && $this->experience_expert_level)) {

                $request_url .= "&contractor_tier=";

                $option_selected = false;

                if ($this->experience_entry_level) {
                    $request_url .= "1";
                    $option_selected = true;
                }

                if ($this->experience_intermediate_level) {
                    if ($option_selected) {
                        $request_url .= ",";
                    }
                    $request_url .= "2";
                    $option_selected = true;
                }

                if ($this->experience_expert_level) {
                    if ($option_selected) {
                        $request_url .= ",";
                    }
                    $request_url .= "3";
                }
            }
        }

        return $request_url;
    }
}