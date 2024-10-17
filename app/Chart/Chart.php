<?php

namespace App\Chart;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Result;
use App\Models\ResultEvent;
use App\Enums\Event;
use App\Enums\ResultStatus;

class Chart
{
    protected $chartData = [];
    protected $options   = [];
    protected $goalEvents = [];

    public function __construct()
    {
        $this->goalEvents = [
            Event::goal->value,
            Event::penalty_goal->value,
            Event::free_kick_goal->value,
        ];
    }

    /**
     * getData 
     *
     * @param array $options 
     *   wdl      : win/draw/loss (chart)
     *   goals    : goals by player (chart)
     *   assists  : assists by player (chart)
     *   gpg      : goals per game
     *   gapg     : goals allowed per game
     *   homeaway : many stats broken down by home/away
     *
     * @param string $results 
     * @param Collection $results 
     * @param Collection $events 
     * 
     * @return array
     */
    public function getData(array $options, string $teamId, Collection $results, ?Collection $events = null)
    {
        $this->options = $options;

        $resultIds = [];

        //
        // Results
        //
        foreach ($results as $result)
        {
            // We only want finished games
            if ($result->status != ResultStatus::Done->value)
            {
                continue;
            }

            $resultIds[$result->id] = $result->id;

            $goodGuys = $result->home_team_id == $teamId ? 'home' : 'away';
            $badGuys  = $goodGuys === 'home'             ? 'away' : 'home';

            $this->getWinDrawLoss($result, $goodGuys, $badGuys);
            $this->getGoalsPerGame($result, $goodGuys);
            $this->getGoalsAllowedPerGame($result, $badGuys);
            $this->getHomeAwayStats($result, $goodGuys, $badGuys);
        }

        if (is_null($events))
        {
            $events = $this->getResultEvents($resultIds);
        }

        //
        // Events
        //
        foreach ($events as $event)
        {
            if ($event->against)
            {
                continue;
            }

            $this->getGoalsPerPlayer($event);
            $this->getAssistsPerPlayer($event);
        }

        $this->formatGoalsPerPlayer();
        $this->formatAssistsPerPlayer();

        return $this->chartData;
    }

    /**
     * getResultEvents 
     * 
     * @param array $resultIds 
     * @return Collection
     */
    private function getResultEvents($resultIds)
    {
        $eventIds = [];

        if (in_array('goals', $this->options) || in_array('assists', $this->options) || in_array('standard', $this->options))
        {
            $eventIds = array_merge($eventIds, $this->goalEvents);
        }

        return ResultEvent::whereIn('result_id', $resultIds)
            ->whereIn('event_id', $eventIds)
            ->get();
    }

    /**
     * getWinDrawLoss 
     * 
     * @param Result $result 
     * @param string $goodGuys 
     * @param string $badGuys 
     * @return null
     */
    private function getWinDrawLoss(Result $result, string $goodGuys, string $badGuys)
    {
        if (!in_array('wdl', $this->options) && !in_array('standard', $this->options))
        {
            return;
        }

        if (!isset($this->chartData['wdl']))
        {
            $this->chartData['wdl'] = ['w' => 0, 'd' => 0, 'l' => 0];
        }

        // win
        if ($result->{$goodGuys . '_team_score'} > $result->{$badGuys . '_team_score'})
        {
            $this->chartData['wdl']['w']++;
        }
        // loss
        else if ($result->{$goodGuys . '_team_score'} < $result->{$badGuys . '_team_score'})
        {
            $this->chartData['wdl']['l']++;
        }
        // draw
        else
        {
            $this->chartData['wdl']['d']++;
        }
    }

    /**
     * getGoalsPerGame 
     * 
     * @param Result $result 
     * @param string $goodGuys 
     * @return null
     */
    private function getGoalsPerGame(Result $result, $goodGuys)
    {
        if (!in_array('gpg', $this->options) && !in_array('standard', $this->options))
        {
            return;
        }

        if (!isset($this->chartData['gpg']))
        {
            $this->chartData['gpg'] = ['goals' => 0, 'games' => 0, 'gpg' => 0];
        }

        $this->chartData['gpg']['goals'] += $result->{$goodGuys . '_team_score'};
        $this->chartData['gpg']['games']++;

        $this->chartData['gpg']['gpg'] = round($this->chartData['gpg']['goals'] / $this->chartData['gpg']['games'], 2);
    }

    /**
     * getGoalsPerPlayer 
     * 
     * @param ResultEvent $event 
     * @return null
     */
    private function getGoalsPerPlayer($event)
    {
        if (!in_array('goals', $this->options) && !in_array('standard', $this->options))
        {
            return;
        }

        if (!isset($this->chartData['goals']))
        {
            $this->chartData['goals'] = ['players' => [], 'labels' => '', 'data' => ''];
        }

        if (!in_array($event->event_id, $this->goalEvents))
        {
            return;
        }

        if (!isset($this->chartData['goals']['players'][$event->player_name]))
        {
            $this->chartData['goals']['players'][$event->player_name] = 0;
        }

        $this->chartData['goals']['players'][$event->player_name]++;
    }

    /**
     * formatGoalsPerPlayer 
     * 
     * Sort goals descending, then by player name alphabetical
     *
     * @return null
     */
    private function formatGoalsPerPlayer()
    {
        if (isset($this->chartData['goals']))
        {
            array_multisort(
                array_values($this->chartData['goals']['players']), 
                SORT_DESC, 
                array_keys($this->chartData['goals']['players']), 
                SORT_ASC, 
                $this->chartData['goals']['players']
            );

            $this->chartData['goals']['labels'] = "'" . implode("','", array_keys($this->chartData['goals']['players'])) . "'";
            $this->chartData['goals']['data']   = implode(',', array_values($this->chartData['goals']['players']));
        }
    }

    /**
     * getAssistsPerPlayer 
     * 
     * @param ResultEvent $event 
     * @return null
     */
    private function getAssistsPerPlayer($event)
    {
        if (!in_array('assists', $this->options) && !in_array('standard', $this->options))
        {
            return;
        }

        if (!isset($this->chartData['assists']))
        {
            $this->chartData['assists'] = ['players' => [], 'labels' => '', 'data' => ''];
        }

        if (!in_array($event->event_id, $this->goalEvents))
        {
            return;
        }

        if (empty($event->additional))
        {
            return;
        }

        if (!isset($this->chartData['assists']['players'][$event->additionalPlayer->name]))
        {
            $this->chartData['assists']['players'][$event->additionalPlayer->name] = 0;
        }

        $this->chartData['assists']['players'][$event->additionalPlayer->name]++;
    }

    /**
     * formatAssistsPerPlayer 
     * 
     * @return null
     */
    private function formatAssistsPerPlayer()
    {
        if (isset($this->chartData['assists']))
        {
            array_multisort(
                array_values($this->chartData['assists']['players']), 
                SORT_DESC, 
                array_keys($this->chartData['assists']['players']), 
                SORT_ASC, 
                $this->chartData['assists']['players']
            );

            $this->chartData['assists']['labels'] = "'" . implode("','", array_keys($this->chartData['assists']['players'])) . "'";
            $this->chartData['assists']['data']   = implode(',', array_values($this->chartData['assists']['players']));
        }
    }

    /**
     * getGoalsAllowedPerGame 
     * 
     * @param Result $result 
     * @param string $badGuys 
     * @return null
     */
    private function getGoalsAllowedPerGame(Result $result, string $badGuys)
    {
        if (!in_array('gapg', $this->options) && !in_array('standard', $this->options))
        {
            return;
        }

        if (!isset($this->chartData['gapg']))
        {
            $this->chartData['gapg'] = ['allowed' => 0, 'games' => 0, 'gapg' => 0];
        }

        $this->chartData['gapg']['allowed'] += $result->{$badGuys . '_team_score'};
        $this->chartData['gapg']['games']++;

        $this->chartData['gapg']['gapg'] = round($this->chartData['gapg']['allowed'] / $this->chartData['gapg']['games'], 2);
    }

    /**
     * getHomeAwayStats 
     * 
     * @param Result $result 
     * @param string $goodGuys 
     * @param string $badGuys 
     * @return null
     */
    private function getHomeAwayStats(Result $result, string $goodGuys, string $badGuys)
    {
        if (!in_array('homeaway', $this->options))
        {
            return;
        }

        if (!isset($this->chartData['homeaway']))
        {
            $stats = [
                'wins'          => 0,
                'draws'         => 0,
                'losses'        => 0,
                'games'         => 0,
                'win_percent'   => '',
                'goals'         => 0,
                'goals_against' => 0,
                'xg'            => 0,
                'xg_against'    => 0,
                'gpg'           => 0,
                'gapg'          => 0,
            ];

            $this->chartData['homeaway'] = [
                'overall' => $stats,
                'home'    => $stats,
                'away'    => $stats,
            ];
        }

        $this->chartData['homeaway']['overall']['games']++;
        $this->chartData['homeaway'][$goodGuys]['games']++;
        $this->chartData['homeaway']['overall']['goals']         += $result->{$goodGuys . '_team_score'};
        $this->chartData['homeaway']['overall']['goals_against'] += $result->{$badGuys . '_team_score'};
        $this->chartData['homeaway'][$goodGuys]['goals']         += $result->{$goodGuys . '_team_score'};
        $this->chartData['homeaway'][$goodGuys]['goals_against'] += $result->{$badGuys . '_team_score'};

        // win
        if ($result->{$goodGuys . '_team_score'} > $result->{$badGuys . '_team_score'})
        {
            $this->chartData['homeaway']['overall']['wins']++;
            $this->chartData['homeaway'][$goodGuys]['wins']++;
        }
        // loss
        else if ($result->{$goodGuys . '_team_score'} < $result->{$badGuys . '_team_score'})
        {
            $this->chartData['homeaway']['overall']['losses']++;
            $this->chartData['homeaway'][$goodGuys]['losses']++;
        }
        // draw
        else
        {
            $this->chartData['homeaway']['overall']['draws']++;
            $this->chartData['homeaway'][$goodGuys]['draws']++;
        }

        // Do some calculations
        if ($this->chartData['homeaway']['overall']['games'])
        {
            $this->chartData['homeaway']['overall']['win_percent'] = round(($this->chartData['homeaway']['overall']['wins'] / $this->chartData['homeaway']['overall']['games']) * 100);

            $this->chartData['homeaway']['overall']['gpg']  = round($this->chartData['homeaway']['overall']['goals'] / $this->chartData['homeaway']['overall']['games'], 2);
            $this->chartData['homeaway']['overall']['gapg'] = round($this->chartData['homeaway']['overall']['goals_against'] / $this->chartData['homeaway']['overall']['games'], 2);
        }
        if ($this->chartData['homeaway'][$goodGuys]['games'])
        {
            $this->chartData['homeaway'][$goodGuys]['win_percent'] = round(($this->chartData['homeaway'][$goodGuys]['wins'] / $this->chartData['homeaway'][$goodGuys]['games']) * 100);

            $this->chartData['homeaway'][$goodGuys]['gpg']  = round($this->chartData['homeaway'][$goodGuys]['goals'] / $this->chartData['homeaway'][$goodGuys]['games'], 2);
            $this->chartData['homeaway'][$goodGuys]['gapg'] = round($this->chartData['homeaway'][$goodGuys]['goals_against'] / $this->chartData['homeaway'][$goodGuys]['games'], 2);
        }
    }
}
