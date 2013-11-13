<?php
/**
 * Definition of Application\Model\Entity\Game
 *
 * @copyright Copyright (c) 2013 The Fußi-Team
 * @license   THE BEER-WARE LICENSE (Revision 42)
 *
 * "THE BEER-WARE LICENSE" (Revision 42):
 * The Fußi-Team wrote this software. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy us a beer in return.
 */

namespace Application\Model\Entity;

use Application\Model\Entity\Game;
use Application\Model\Entity\League;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for all match types. Matches of all types are stored in a
 * single database table (Doctrine's Single Table Inheritance).
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#single-table-inheritance
 *
 * @ORM\Entity(repositoryClass="Application\Model\Repository\MatchRepository")
 * @ORM\Table(name="match")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"single" = "Application\Model\Entity\SingleMatch", "double" = "Application\Model\Entity\DoubleMatch"})
 */
abstract class Match
{

    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AbstractTournament
     *
     * @ORM\ManyToOne(targetEntity="\Application\Model\Entity\AbstractTournament")
     * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id")
     */
    protected $tournament;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime",nullable=false)
     */
    protected $date;

    /**
     * @var Game[]
     *
     * @ORM\OneToMany(targetEntity="Application\Model\Entity\Game", mappedBy="match", cascade={"persist"})
     */
    protected $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Score of the match (won games for each team)
     *
     * @return string
     */
    public function getScore()
    {
        $score = $this->getRawScore();
        return $score[0] . " / " . $score[1];
    }

    /**
     * Returns an array with the number of won games for each team
     *
     * @return array Index 0: Won games team one, index 1: won games team two
     */
    protected function getRawScore()
    {
        $win1 = 0;
        $win2 = 0;

        foreach ($this->games as $game) {

            if ($game->getGoalsTeamOne() > $game->getGoalsTeamTwo()) {
                $win1++;
            } elseif ($game->getGoalsTeamTwo() > $game->getGoalsTeamOne()) {
                $win2++;
            }

        }

        return array($win1, $win2);
    }

    /**
     * @param AbstractTournament $tournament
     */
    public function setTournament(AbstractTournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * @return AbstractTournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Returns the winning team, either One or Two
     *
     * @return int
     */
    public function getWinner()
    {
        $score = $this->getRawScore();
        if ($score[0] > $score[1]) {
            return 1;
        }
        if ($score[0] < $score[1]) {
            return 2;
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function isTeamOneWinner()
    {
        return $this->getWinner() == 1;
    }

    /**
     * @return bool
     */
    public function isTeamTwoWinner()
    {
        return $this->getWinner() == 2;
    }

    /**
     * @param Game $game
     */
    public function addGame(Game $game)
    {
        $game->setMatch($this);
        $this->games[] = $game;
    }

    /**
     * @param Game[] $games
     */
    public function setGames($games)
    {
        $this->games = new ArrayCollection($games);
        foreach ($this->games as $game) {
            $game->setMatch($this);
        }
    }

    /**
     * @return Game[]
     */
    public function getGames()
    {
        return $this->games;
    }


}